<?php

if (!function_exists('email_styles')) {
    function email_styles(): array
    {
        return [
            'body' => "
                font-family: 'Trebuchet MS', Tahoma, Arial, sans-serif;
                color: #111;
                font-size: 18px;
                line-height: 1.4;
                text-align: center;
                background-color: #f1adbd22;
                padding: 0.8em;
                max-width: 750px;
                margin: 0 auto;",
            'h3' => '
                color: #F62456;
                background-color: #f1adbd33;
                padding: 10px;
                border-bottom: 0px solid #F62456;',
            'btn' => '
                display: inline-block;
                background: #F62456;
                padding: 10px;
                color: white;
                text-decoration: none;
                border: 1px solid #F62456;
                border-radius: 0.5em;
                min-width: 150px;',
            'text-center' => '
                text-align: center;',
            'footer' => '
                font-size: 0.8em;
                color: #777;
                text-align: center;
                margin-top: 10px;'
        ];
    }
}
