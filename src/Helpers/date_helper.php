<?php
function getStartAndEndDate($week, $year) {
  $dto = new DateTime();
  $dto->setISODate($year, $week);
  $ret['week_start'] = $dto->format('o-m-d');
  $dto->modify('+6 days');
  $ret['week_end'] = $dto->format('o-m-d');
  return $ret;
}