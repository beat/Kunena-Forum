<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

defined('_JEXEC') or die();

use Kunena\Forum\Libraries\Config\KunenaConfig;
use function defined;

if (KunenaConfig::getInstance()->lightbox != 1)
{
	return false;
}

$this->addStyleSheet('fancybox.css');
$this->addScript('fancybox-min.js');
