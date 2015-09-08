<?php

/**
 * MailModule provides messaging functions inside the application.
 *
 * @package humhub.modules.mail
 * @since 0.5
 */
class MailModule extends HWebModule
{

    public function init()
    {

        $this->setImport(array(
            'mail.models.*',
            'mail.controllers.*',
            'mail.behaviors.*',
            'mail.forms.*',
        ));
    }

    /**
     * On User delete, also delete all comments
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        Yii::import('application.modules.mail.models.*');

        // Delete all message entries
        foreach (MessageEntry::model()->findAllByAttributes(array('user_id' => $event->sender->id)) as $messageEntry) {
            $messageEntry->delete();
        }

        
        // Leaves all my conversations
        foreach (UserMessage::model()->findAllByAttributes(array('user_id' => $event->sender->id)) as $userMessage) {
            $userMessage->message->leave($event->sender->id);
        }

        return true;
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        #$integrityChecker->showTestHeadline("Validating Mail Module (" . Message::model()->count() . " entries)");
    }

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event)
    {
        if (Yii::app()->user->isGuest) {
            return;
        }

        $event->sender->addItem(array(
            'label' => Yii::t('MailModule.base', 'Messages'),
            'url' => Yii::app()->createUrl('//mail/mail/index', array()),
            'icon' => '<i class="fa fa-envelope"></i>',
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'mail'),
            'sortOrder' => 300,
        ));
    }

    public static function onNotificationAddonInit($event)
    {
        if (Yii::app()->user->isGuest) {
            return;
        }

        $event->sender->addWidget('application.modules.mail.widgets.MailNotificationWidget', array(), array('sortOrder' => 90));
    }

    public static function onProfileHeaderControlsInit($event)
    {
        if (Yii::app()->user->isGuest || $event->sender->user->id == Yii::app()->user->id) {
            return;
        }

        $event->sender->addWidget('application.modules.mail.widgets.NewMessageButtonWidget', array('guid' => $event->sender->user->guid, 'type' => 'success'), array('sortOrder' => 90));
    }

}