<?php 
function set_value($name,$default = ''){
	return (!empty($_POST[$name]))? trim(htmlspecialchars($_POST[$name])): $default;
}

function pagination($total,$per_page,$num_links,$start_row,$url=''){
	//�������� ����� ����� �������
	$num_pages = ceil($total/$per_page); 
	
	if ($num_pages <= 1) return '';
	
	//�������� ���������� ��������� �� ��������
	$cur_page = $start_row; 
	
	//���� ���������� ��������� �� �������� ������ ��� ����� ����� ���������
	// �� ������� �������� ����� ����� ���������
	if ($cur_page > $total){
		$cur_page = ($num_pages - 1) * $per_page;
	}
	
	//�������� ����� ������� ��������
	$cur_page = floor(($cur_page/$per_page) + 1);
	
	//�������� ����� ��������� �������� ��������� � ���������
	$start = (($cur_page - $num_links) > 0) ? $cur_page - $num_links : 0;
	//�������� ����� ��������� �������� ��������� � ���������
	$end   = (($cur_page + $num_links) < $num_pages) ? $cur_page + $num_links : $num_pages;
	
	$output = '<span class="ways">';
	
	//��������� ������ �� ���������� ��������
	if  ($cur_page != 1){
			$i = $start_row - $per_page;
			if ($i <= 0) $i = 0;
			$output .= '<i>?</i><a href="'.$url.'?p='.$i.'">����������</a>';
	}
	else{
		$output .= '<span><i>?</i>����������</span>';
	}
	
	$output .= '<span class="divider">|</span>';
	
	//��������� ������ �� ��������� ��������
	if ($cur_page < $num_pages){
		$output .= '<a href="'.$url.'?p='.($cur_page * $per_page).'">���������</a><i>?</i>';
	}
	else{
		$output .= '<span>���������<i>?</i></span>';
	}
	
	$output .= '</span><br/>';
	
	
	//��������� ������ �� ������ ��������
	if  ($cur_page > ($num_links + 1)){
		$output .= '<a href="'.$url.'" title="First"><img src="images/left_active.png"></a>';
	}
	
	// ��������� ������ ������� � ������ ��������� � ��������� ��������	
    for ($loop = $start; $loop <= $end; $loop++){
		$i = ($loop * $per_page) - $per_page;

		if ($i >= 0)
		{
			if ($cur_page == $loop)
			{
				$output .= '<span>'.$loop.'</span>'; // ������� ��������
			}
			else
			{
				$n = ($i == 0) ? '' : $i;
				$output .= '<a href="'.$url.'?p='.$n.'">'.$loop.'</a>';
			}
		}
	}

	//��������� ������ �� ��������� ��������
	if (($cur_page + $num_links) < $num_pages){
		$i = (($num_pages * $per_page) - $per_page);
		$output .= '<a href="'.$url.'?p='.$i.'" title="Last"><img src="images/right_active.png"></a>';
	}
	
	
	
	return '<div class="wrapPaging"><strong>Pages:</strong>'.$output.'</div>';
}

function format_date($date,$format = 'date'){
    if(empty($date)) return '';

    $months = array(
        '1' => 'January',
        '2' => 'February',
        '3' => 'March',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December'
    );

    if($format == 'time'){
        return date('H:i',$date);
    }
    elseif($format == 'date'){

        $m = date('n', $date); $m = $months[$m];

        $d = date('j',$date);

        $y = date('Y',$date);

        return  $d.' '.$m.' '.$y;

    }
    else{
        return date('d.M.Y H:i',$date);
    }
}
?>
