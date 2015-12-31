<?php

namespace Steelbot\Context;

/**
 * Interface ContextHelpInterface
 * @package Steelbot\Context
 */
interface ContextHelpInterface
{
    const HELP_LIST = 'list';
    const HELP_SHORT = 'short';
    const HELP_MEDIUM = 'medium';
    const HELP_FULL = 'full';

    /**
     * Get context description
     *
     * @param string $format    short|medium|full
     *
     * @return string
     */
    public function getHelp(string $format): string;
}
