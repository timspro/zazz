<?php

require_once dirname(__FILE__) . '/../includes/standard/initialize.php';
require_once dirname(__FILE__) . '/../includes/custom/functions.php';

Authenticate::get()->check(false);

if (verifyPage($_REQUEST['page_id'], Authenticate::get()->getUser('user_id'))) {
	if (isset($_REQUEST['layout']) && isset($_REQUEST['page_id'])) {
		_Layout::get()->update($_REQUEST, array('page_id' => $_REQUEST['page_id']));
	}

	if (isset($_REQUEST['to']) && isset($_REQUEST['from'])) {
		//JavaScript should have updated this page but we will also need to update pages that have this 
		//as a template.
		$new = $_REQUEST['to'];
		$old = $_REQUEST['from'];
		_Code::get()->update(array('zazz_id' => $new), array('zazz_id' => $old,
				'page_id' => $_REQUEST['page_id']));
		
		
		
		
		//Get all pages that have the current page as a template. 
		require_once dirname(__FILE__) . '/../includes/custom/simple_html_dom.php';

		$layouts = _Template::get()->retrieve(array('page_id', 'layout'), 
				array(new Join('page_id', _Layout::get()),
				new Join('page_id', _Page::get())), array('template' => $_REQUEST['page_id']));
		$first = true;
		foreach ($layouts as $layout) {
			try {
				_Code::get()->update(array('zazz_id' => $new), array('zazz_id' => $old,
					'page_id' => $layout['page_id']));
			} catch (PDOException $e) {
				if ($first) {
					echo 'Since there was already an element with the ID of ' . $new . ', could not update:<br>';
					$first = false;
				}
				echo $layout['page'] . '<br>';
			}
			$html = new simple_html_dom();
			$html->load($layout['layout']);

			$element = $html->find('.-zazz-element[data-zazz-id="' . $old . '"]', 0);
			if ($element !== null) {
				$element->setAttribute('data-zazz-id', $new);
				$element->setAttribute('id', $new);
			}
			$newLayout = $html->save();
			_Layout::get()->update(array('layout' => $newLayout), array('page_id' => $layout['page_id']));
		}
	}
}
?>
