<?php

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HelpersExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('log_error', [Helpers::class, 'log_error']),
            new TwigFunction('log_info', [Helpers::class, 'log_info']),
            new TwigFunction('fetch_from_session', [Helpers::class, 'fetch_session_data']),
            new TwigFunction('is_logged_in', [Helpers::class, 'is_logged_in']),
            new TwigFunction('is_admin', [Helpers::class, 'is_admin']),
            new TwigFunction('is_student', [Helpers::class, 'is_student']),
            new TwigFunction('flash_error', [Helpers::class, 'flash_error']),
            
        ];
    }
}
