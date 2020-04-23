<?php
/**
 * 评论通知推送多服务
 *
 * @package CommentPush
 * @author 高彬展,奥秘Sir
 * @version 1.0.0
 * @link https://github.com/gaobinzhan/CommentPush
 */

require 'lib/QQService.php';
require 'lib/WeChatService.php';

class CommentPush_Plugin implements Typecho_Plugin_Interface
{
    protected static $comment;
    protected static $active;

    /**
     * @return string|void
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->comment = [__CLASS__, 'pushServiceReady'];
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = [__CLASS__, 'pushServiceGo'];
        return _t('CommentPush插件启用成功');
    }


    public static function deactivate()
    {
        // TODO: Implement deactivate() method.
    }

    /**
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $services = new Typecho_Widget_Helper_Form_Element_Checkbox('services', [
            "QQService" => "Qmsg酱",
            "WeChatService" => 'Server酱'
        ], 'services', _t('选择通知服务 多选同时推送'), _t('插件作者：<br><a href="https://www.gaobinzhan.com">高彬展</a><br><a href="https://blog.say521.cn/">奥秘Sir</a>'));
        $form->addInput($services->addRule('required', _t('必须选择一项通知服务')));

        $qqApiUrl = new Typecho_Widget_Helper_Form_Element_Text('qqApiUrl', NULL, NULL, _t('Qmsg酱接口'), _t("当选择Qmsg酱必须填写"));
        $form->addInput($qqApiUrl);

        $receiveQq = new Typecho_Widget_Helper_Form_Element_Text('receiveQq', NULL, NULL, _t('接收消息的QQ，可以添加多个，以英文逗号分割'), _t("当选择Qmsg酱必须填写（指定的QQ必须在您的QQ号列表中）"));
        $form->addInput($receiveQq);


        $weChatScKey = new Typecho_Widget_Helper_Form_Element_Text('weChatScKey', NULL, NULL, _t('Server酱 SCKEY'), _t("当选择Server酱必须填写"));
        $form->addInput($weChatScKey);

        $isPushBlogger = new Typecho_Widget_Helper_Form_Element_Radio('isPushBlogger', [
            1 => '是',
            0 => '否'
        ], 1, _t('当评论者为博主本人不推送'), _t('如果选择“是”，博主本人写的评论将不推送'));
        $form->addInput($isPushBlogger);
    }

    /**
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // TODO: Implement personalConfig() method.
    }


    public static function pushServiceReady($comment, $active)
    {
        self::$comment = $comment;
        self::$active = $active;

        return $comment;
    }

    public static function pushServiceGo($comment)
    {
        $options = Helper::options();
        $plugin = $options->plugin('CommentPush');

        $isPushBlogger = $plugin->isPushBlogger;

        if (self::$comment['authorId'] == 1 && $isPushBlogger == 1) return false;

        $services = $plugin->services;

        if (!$services || $services == 'services') return false;


        self::$comment['coid'] = $comment->coid;

        /** @var QQService | WeChatService $service */
        foreach ($services as $service) call_user_func([$service, '__handler'], self::$active, self::$comment, $plugin);
    }
}