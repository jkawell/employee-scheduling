<?php
namespace Spark\Project\Domain\Entity\Mapper;

use Spot\Mapper;

class Shift extends Mapper
{
    /**
     * Get 10 most recent shifts for this employee
     *
     * @return \Spot\Query
     */
    public function getAllShifts()
    {
        return $this->where(['id' => $this->id])
            ->order(['date_created' => 'DESC'])
            ->limit(10);
    }
    
    public function getShiftsBetween($start_time, $end_time)
    {
        return $this->where(['start_time >=' => $start_time, 'end_time <=' => $end_time])
                    ->order(['start_time' => 'ASC']);
    }
    
    public function getCoworkerShifts($shift_id)
    {
        $shift = $this->where(['id' => $shift_id])->first();
		/* 
		 * start_time has to be between start/end
		 * OR end_time has to be between start/end
		 * OR start_time is before start AND end_time is after end
		 */
		$start = $shift->start_time->date;
		$end = $shift->end_time->date;
		$query = <<<SQL
SELECT u.id AS user_id, u.name, s.start_time, s.end_time FROM `shift` s
LEFT JOIN `user` u ON (s.employee_id = u.id)
WHERE s.id != {$shift->id}
AND ((start_time >= '{$start}' && start_time <= '{$end}')
OR (end_time >= '{$start}' && end_time <= '{$end}')
OR (start_time < '{$start}' && end_time > '{$end}'))
ORDER BY name ASC;
SQL;
        return $this->query($query);
    }
    
    public function getWeeklyHours($employee_id, $weeks_back = 5)
    {
        $weekly_hours = [];
        $current_week_number = date('W');
        $current_year_number = date('Y');
        //get array of [week_start, week_end, hours], account for breaks
        for($i = $weeks_back - 1; $i >= 0; $i--)
        {
            $week_number = $current_week_number - $i;
            $week_dates = getStartAndEndDate($week_number, $current_year_number);
		$query = <<<SQL
SELECT ROUND(SUM(TIMESTAMPDIFF(SECOND,start_time,end_time)/3600), 2) AS hours FROM shift
WHERE employee_id = 1
AND start_time >= '{$week_dates['week_start']}'
AND start_time <= '{$week_dates['week_end']}';
SQL;
            $result = $this->query($query)->first();
            $week_hours['week_start'] = date(\DateTime::RFC2822, strtotime($week_dates['week_start']));
            $week_hours['week_end'] = date(\DateTime::RFC2822, strtotime($week_dates['week_end']));
            $week_hours['hours'] = (empty($result->hours) ? 0 : $result->hours);
            $weekly_hours[] = $week_hours;
        }
        return $weekly_hours;
    }
    
    public function getConflictingShifts($employee_id, $start, $end)
    {
		$query = <<<SQL
SELECT * FROM `shift`
WHERE employee_id = {$employee_id}
AND (
(start_time >= '{$start}' && start_time <= '{$end}')
OR (end_time >= '{$start}' && end_time <= '{$end}')
OR (start_time < '{$start}' && end_time > '{$end}')
)
ORDER BY start_time ASC;
SQL;
        return $this->query($query);
    }
}