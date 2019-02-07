<?php

namespace Cleanse\User;

use App;
use Auth;
use Event;
use Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Illuminate\Foundation\AliasLoader;
use Cleanse\User\Models\MailBlocker;

class Plugin extends PluginBase
{
    /**
     * @var boolean Determine if this plugin should have elevated privileges.
     */
    public $elevated = true;

    public function pluginDetails()
    {
        return [
            'name' => 'PvPaissa User Plugin',
            'description' => 'Custom account component based on RainLab.User.',
            'author' => 'Paul Lovato',
            'icon' => 'icon-user',
            'homepage' => 'https://github.com/pvpaissa/user'
        ];
    }

    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Auth', 'Cleanse\User\Facades\Auth');

        App::singleton('user.auth', function () {
            return \Cleanse\User\Classes\AuthManager::instance();
        });

        /*
         * Apply user-based mail blocking
         */
        Event::listen('mailer.prepareSend', function ($mailer, $view, $message) {
            return MailBlocker::filterMessage($view, $message);
        });
    }

    public function registerComponents()
    {
        return [
            'Cleanse\User\Components\Session' => 'cleanseSession',
            'Cleanse\User\Components\Account' => 'cleanseAccount',
            'Cleanse\User\Components\ResetPassword' => 'cleanseResetPassword'
        ];
    }

    public function registerPermissions()
    {
        return [
            'cleanse.users.access_users' => [
                'tab' => 'cleanse.user::lang.plugin.tab',
                'label' => 'cleanse.user::lang.plugin.access_users'
            ],
            'cleanse.users.access_groups' => [
                'tab' => 'cleanse.user::lang.plugin.tab',
                'label' => 'cleanse.user::lang.plugin.access_groups'
            ],
            'cleanse.users.access_settings' => [
                'tab' => 'cleanse.user::lang.plugin.tab',
                'label' => 'cleanse.user::lang.plugin.access_settings'
            ]
        ];
    }

    public function registerNavigation()
    {
        return [
            'user' => [
                'label' => 'cleanse.user::lang.users.menu_label',
                'url' => Backend::url('cleanse/user/users'),
                'icon' => 'icon-user',
                'iconSvg' => 'plugins/cleanse/user/assets/images/user-icon.svg',
                'permissions' => ['cleanse.users.*'],
                'order' => 500,
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'cleanse.user::lang.settings.menu_label',
                'description' => 'cleanse.user::lang.settings.menu_description',
                'category' => SettingsManager::CATEGORY_USERS,
                'icon' => 'icon-cog',
                'class' => 'Cleanse\User\Models\Settings',
                'order' => 500,
                'permissions' => ['cleanse.users.access_settings'],
            ]
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'cleanse.user::mail.activate' => 'Activation email sent to new users.',
            'cleanse.user::mail.welcome' => 'Welcome email sent when a user is activated.',
            'cleanse.user::mail.restore' => 'Password reset instructions for front-end users.',
            'cleanse.user::mail.new_user' => 'Sent to administrators when a new user joins.',
            'cleanse.user::mail.reactivate' => 'Notification for users who reactivate their account.',
        ];
    }
}
