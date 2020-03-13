<?php
use Laravel\Lumen\Routing\Router;

// 注册开放、登录和权鉴三套路由
app()->router->group(['prefix' => 'v1'], function (Router $router) {
    /**
     * 公共路由
     */
    $router->post('tokens/register', 'TokenController@register');
    $router->get('tokens/check', 'TokenController@check');
    $router->put('tokens', 'TokenController@update');
    $router->post('tokens', 'TokenController@create');
    $router->get('allow', 'TokenController@allow');

    /**
     * 登录路由
     */
    app()->router->group(['middleware' => ['jwt', 'checkip']], function (Router $router) {
        // token
        $router->delete('tokens', 'TokenController@delete');
        $router->get('tokens/info', 'TokenController@show');
        $router->put('tokens/password', 'TokenController@password');
        $router->put('tokens/profile', 'TokenController@profile');

        // account
        $router->get('accounts', 'AccountController@index');
        $router->post('accounts/deposit', 'AccountController@deposit');
        $router->post('accounts/withdrawal', 'AccountController@withdrawal');
        $router->patch('accounts/{id:\d+}', 'AccountController@patch');
        $router->put('balances/{id:\d+}', 'AccountController@balance');

        // trade
        $router->get('trades', 'TradeController@index');
        $router->post('trades', 'TradeController@create');

        // forex
        $router->get('forexes', 'ForexController@index');

        // order
        $router->get('orders', 'OrderController@index');

        // message
        $router->get('messages', 'MessageController@index');
        $router->get('messages/{id:\d+}', 'MessageController@show');
        $router->post('messages', 'MessageController@create');
        $router->put('messages/{id:\d+}', 'MessageController@update');
        $router->post('messages/{id:\d+}/reply', 'MessageController@reply');
        $router->put('messages/{id:\d+}/confirm', 'MessageController@confirm');
        $router->delete('messages', 'MessageController@delete');

        // members
        $router->get('members', 'MemberController@index');
        $router->get('members/{id:\d+}', 'MemberController@show');
        $router->put('members/{id:\d+}', 'MemberController@update');
        $router->delete('members', 'MemberController@delete');
        $router->patch('members/{id:\d+}', 'MemberController@toggleEnable');
        $router->get('members/export', 'MemberController@export');

        // agents
        $router->get('agents', 'AgentController@index');
        $router->get('agents/{id:\d+}', 'AgentController@show');
        $router->post('agents', 'AgentController@create');
        $router->put('agents/{id:\d+}', 'AgentController@update');
        $router->delete('agents', 'AgentController@delete');
        $router->patch('agents/{id:\d+}', 'AgentController@toggleEnable');

        // datas
        $router->get('datas/agents', 'DataController@agents');
        $router->get('datas/statistics', 'DataController@statistics');
        $router->post('datas/clean', 'DataController@clean');

        // settings
        $router->get('settings', 'SettingController@index');
        $router->put('settings', 'SettingController@update');
        $router->put('settings/ips', 'SettingController@updateIp');
        $router->get('settings/pops', 'SettingController@pops');
        $router->post('settings/pops', 'SettingController@createPop');
        $router->put('settings/pops/{id:\d+}', 'SettingController@updatePop');
        $router->patch('settings/pops/{id:\d+}', 'SettingController@toggleEnable');
        $router->post('upload', 'SettingController@upload');

        // chats
        $router->get('chats', 'ChatController@index');
        $router->get('chats/{id:\d+}', 'ChatController@show');
        $router->post('chats', 'ChatController@create');
    });
});
