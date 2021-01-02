<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Administrator.Template
 * @subpackage      Smilies
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;
use Kunena\Forum\Libraries\Version\KunenaVersion;
use Kunena\Forum\Libraries\Route\KunenaRoute;

/** @var WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('multiselect');
?>

<script type="text/javascript">
	Joomla.orderTable = function () {
	  const table = document.getElementById("sortTable");
	  const direction = document.getElementById("directionTable");
	  const order = table.options[table.selectedIndex].value;

	  if (order !== '<?php echo $this->list->Ordering; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<div id="kunena" class="container-fluid">
	<div class="row">
		<div id="j-main-container" class="col-md-12" role="main">
			<div class="card card-block bg-faded p-2">
				<div class="tabbable-panel">
					<div class="tabbable-line">
						<ul class="nav nav-tabs">
							<li class="nav-item">
								<a class="nav-link active" href="#tab1"
								   data-toggle="tab"><?php echo Text::_('COM_KUNENA_A_EMOTICONS'); ?></a></li>
							<li class="nav-item"><a class="nav-link" href="#tab2"
													data-toggle="tab"><?php echo Text::_('COM_KUNENA_A_EMOTICONS_UPLOAD'); ?></a>
							</li>
						</ul>
						<div class="tab-content" style="padding-bottom: 9px; border-bottom: 1px solid #ddd;">
							<div class="tab-pane  active" id="tab1">

								<form action="<?php echo KunenaRoute::_('administrator/index.php?option=com_kunena&view=smilies') ?>"
									  method="post"
									  id="adminForm" name="adminForm">
									<input type="hidden" name="task" value=""/>
									<input type="hidden" name="boxchecked" value="0"/>
									<input type="hidden" name="filter_order"
										   value="<?php echo $this->list->Ordering; ?>"/>
									<input type="hidden" name="filter_order_Dir"
										   value="<?php echo $this->list->Direction; ?>"/>
									<?php echo HTMLHelper::_('form.token'); ?>

									<div id="filter-bar" class="btn-toolbar">
										<div class="filter-search btn-group pull-left">
											<label for="filter_search"
												   class="element-invisible"><?php echo Text::_('COM_KUNENA_FIELD_LABEL_SEARCHIN'); ?></label>
											<input type="text" name="filter_search" id="filter_search"
												   class="filter form-control"
												   placeholder="<?php echo Text::_('COM_KUNENA_ATTACHMENTS_FIELD_INPUT_SEARCHFILE'); ?>"
												   value="<?php echo $this->filter->Search; ?>"
												   title="<?php echo Text::_('COM_KUNENA_SMILIES_FIELD_INPUT_SEARCHSMILIES'); ?>"/>
										</div>
										<div class="btn-group pull-left">
											<button class="btn btn-outline-primary tip" type="submit"
													title="<?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT'); ?>">
												<i class="icon-search"></i> <?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>
											</button>
											<button class="btn btn-outline-primary tip" type="button"
													title="<?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERRESET'); ?>"
													onclick="jQuery('.filter').val('');jQuery('#adminForm').submit();">
												<i class="icon-remove"></i> <?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERRESET'); ?>
											</button>
										</div>
										<div class="btn-group pull-right hidden-phone">
											<?php echo $this->pagination->getLimitBox(); ?>
										</div>
										<div class="btn-group pull-right hidden-phone">
											<label for="directionTable"
												   class="element-invisible"><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></label>
											<select name="directionTable" id="directionTable" class="input-medium"
													onchange="Joomla.orderTable()">
												<option value=""><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></option>
												<?php echo HTMLHelper::_('select.options', $this->sortDirectionFields, 'value', 'text', $this->list->Direction); ?>
											</select>
										</div>
										<div class="btn-group pull-right">
											<label for="sortTable"
												   class="element-invisible"><?php echo Text::_('JGLOBAL_SORT_BY'); ?></label>
											<select name="sortTable" id="sortTable" class="input-medium"
													onchange="Joomla.orderTable()">
												<option value=""><?php echo Text::_('JGLOBAL_SORT_BY'); ?></option>
												<?php echo HTMLHelper::_('select.options', $this->sortFields, 'value', 'text', $this->list->Ordering); ?>
											</select>
										</div>
									</div>

									<table class="table table-striped" id="smileyList">
										<thead>
										<tr>
											<th width="1%" class="center">
												<input type="checkbox" name="toggle" value=""
													   onclick="Joomla.checkAll(this)"/>
											</th>
											<th width="5%"
												class="center"><?php echo Text::_('COM_KUNENA_EMOTICON'); ?></th>
											<th width="8%"><?php echo HTMLHelper::_('grid.sort', 'COM_KUNENA_EMOTICONS_CODE', 'code', $this->list->Direction, $this->list->Ordering); ?></th>
											<th><?php echo HTMLHelper::_('grid.sort', 'COM_KUNENA_EMOTICONS_URL', 'location', $this->list->Direction, $this->list->Ordering); ?></th>
											<th width="1%" class="nowrap center hidden-phone">
												<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'id', $this->list->Direction, $this->list->Ordering); ?>
											</th>
										</tr>
										<tr>
											<td class="hidden-phone center">
											</td>
											<td class="hidden-phone center">
											</td>
											<td class="nowrap center">
												<label for="filter_code"
													   class="element-invisible"><?php echo Text::_('COM_KUNENA_FIELD_LABEL_SEARCHIN') ?></label>
												<input class="input-block-level input-filter filter form-control"
													   type="text"
													   name="filter_code" id="filter_code"
													   placeholder="<?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>"
													   value="<?php echo $this->filter->Code; ?>"
													   title="<?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>"/>
											</td>
											<td class="nowrap center">
												<label for="filter_location"
													   class="element-invisible"><?php echo Text::_('COM_KUNENA_FIELD_LABEL_SEARCHIN') ?></label>
												<input class="input-block-level input-filter filter form-control"
													   type="text"
													   name="filter_location"
													   id="filter_location"
													   placeholder="<?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>"
													   value="<?php echo $this->filter->Location; ?>"
													   title="<?php echo Text::_('COM_KUNENA_SYS_BUTTON_FILTERSUBMIT') ?>"/>
											</td>
											<td class="hidden-phone center">
											</td>
										</tr>
										</thead>
										<tfoot>
										<tr>
											<td colspan="5">
												<?php echo $this->pagination->getListFooter(); ?>
											</td>
										</tr>
										</tfoot>
										<tbody>
										<?php
										$i = 0;

										if ($this->pagination->total > 0)
											:
											foreach ($this->items as $id => $row)
												:
												?>
												<tr>
													<td class="hidden-phone center">
														<input type="checkbox" id="cb<?php echo $id; ?>" name="cid[]"
															   value="<?php echo $this->escape($row->id); ?>"
															   onclick="Joomla.isChecked(this.checked);"/>
													</td>
													<td class="hidden-phone center">
														<a href="#edit"
														   onclick="return Joomla.listItemTask('cb<?php echo $id; ?>','edit')">
															<img loading="lazy" src="<?php echo $this->escape($this->ktemplate->getSmileyPath($row->location, true)); ?>"
																 alt="<?php echo $this->escape($row->location); ?>"/>
														</a>
													</td>
													<td class="hidden-phone">
														<?php echo $this->escape($row->code); ?>
													</td>
													<td>
														<?php echo $this->escape($row->location); ?>
													</td>
													<td>
														<?php echo $this->escape($row->id); ?>
													</td>
												</tr>
											<?php
											endforeach;
										else:
											?>
											<tr>
												<td colspan="10">
													<div class="card card-block bg-faded p-2 center filter-state">
															<span><?php echo Text::_('COM_KUNENA_FILTERACTIVE'); ?>
																<?php
																if ($this->filter->Active)
																	:
																	?>
																	<button class="btn btn-outline-primary"
																			type="button"
																			onclick="document.getElements('.filter').set('value', '');this.form.submit();"><?php echo Text::_('COM_KUNENA_FIELD_LABEL_FILTERCLEAR'); ?></button>
																<?php else

																	:
																	?>
																	<button class="btn btn-outline-success"
																			type="button"
																			onclick="Joomla.submitbutton('add');"><?php echo Text::_('COM_KUNENA_NEW_SMILIE'); ?></button>
																<?php endif; ?>
															</span>
													</div>
												</td>
											</tr>
										<?php endif; ?>
										</tbody>
									</table>
								</form>
							</div>

							<div class="tab-pane" id="tab2">
								<form action="<?php echo KunenaRoute::_('administrator/index.php?option=com_kunena') ?>"
									  id="uploadForm" method="post"
									  enctype="multipart/form-data">
									<input type="hidden" name="view" value="smilies"/>
									<input type="hidden" name="task" value="smileyupload"/>
									<input type="hidden" name="boxchecked" value="0"/>
									<?php echo HTMLHelper::_('form.token'); ?>

									<input type="file" id="file-upload" class="btn btn-outline-primary"
										   name="Filedata"/>
									<input type="submit" id="file-upload-submit" class="btn btn-outline-primary"
										   value="<?php echo Text::_('COM_KUNENA_A_START_UPLOAD'); ?>"/>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>

	<div class="pull-right small">
		<?php echo KunenaVersion::getLongVersionHTML(); ?>
	</div>
</div>
