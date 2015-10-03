<?php

/**
 * @var \Steelbot\Context\IncludeFileContext $this
 */

$this->answer("You enter ".$message);

/**
 * @var \Psr\Log\LoggerInterface $logger
 */
$logger = $this->app->getLogger();

$logger->error("Some error");
$logger->alert("Some alert");
$logger->critical("Some critical");
$logger->emergency("Some emergency");
$logger->notice("Some  notice");
$logger->warning("Some  warning");