<?php
/*
  $Id: banner_daily.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('external/panachart/panachart.php');

  $views = array();
  $clicks = array();
  $vLabels = array();

  $year = isset($_REQUEST['year']) && !empty($_REQUEST['month']) ? $_REQUEST['year'] : date('Y');
  $month = isset($_REQUEST['month']) && !empty($_REQUEST['month']) ? $_REQUEST['month'] : date('n');
  $days = date('t', mktime(0, 0, 0, $month))+1;
  $stats = array();

  for ( $i = 1; $i < $days; $i++ ) {
    $stats[] = array($i, '0', '0');

    $views[$i-1] = 0;
    $clicks[$i-1] = 0;
    $vLabels[] = $i;
  }

  $Qstats = $osC_Database->query('select dayofmonth(banners_history_date) as banner_day, banners_shown as value, banners_clicked as dvalue from :table_banners_history where banners_id = :banners_id and month(banners_history_date) = :month and year(banners_history_date) = :year');
  $Qstats->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
  $Qstats->bindInt(':banners_id', $_REQUEST['banners_id']);
  $Qstats->bindInt(':month', $month);
  $Qstats->bindInt(':year', $year);
  $Qstats->execute();

  while ( $Qstats->next() ) {
    $stats[($Qstats->valueInt('banner_day')-1)] = array($Qstats->valueInt('banner_day'), (($Qstats->valueInt('value') > 0) ? $Qstats->valueInt('value') : '0'), (($Qstats->valueInt('dvalue') > 0) ? $Qstats->valueInt('dvalue') : '0'));

    $views[($Qstats->valueInt('banner_day')-1)] = $Qstats->valueInt('value');
    $clicks[($Qstats->valueInt('banner_day')-1)] = $Qstats->valueInt('dvalue');
  }

  $ochart = new chart(600,350, 5, '#eeeeee');
  $ochart->setTitle(sprintf($osC_Language->get('subsection_heading_statistics_daily'), $title, strftime('%B', mktime(0, 0, 0, $month)), $year), '#000000', 2);
  $ochart->setPlotArea(SOLID, '#444444', '#dddddd');
  $ochart->setFormat(0, ',', '.');
  $ochart->setXAxis('#000000', SOLID, 1, '');
  $ochart->setYAxis('#000000', SOLID, 2, '');
  $ochart->setLabels($vLabels, '#000000', 1, VERTICAL);
  $ochart->setGrid('#bbbbbb', DASHED, '#bbbbbb', DOTTED);
  $ochart->addSeries($views, 'area', 'Series1', SOLID, '#000000', '#0000ff');
  $ochart->addSeries($clicks, 'area', 'Series1', SOLID, '#000000', '#ff0000');
  $ochart->plot('images/graphs/banner_daily-' . $_REQUEST['banners_id'] . '.png');
?>
