<?php/* * Copyright (c) 2023. * RW::Software * Dave Conley * https://www.rwscripts.com/ */require_once ROOT_PATH . "/functions/files.php";class mysqlSearch extends files{	var $shtml;	var $output;	var $entry_identifier;	var $searchcolumns;	var $table;	var $numresults;	function mysqlSearch()	{		global $IN, $OUTPUT, $std;		$this->shtml = $OUTPUT->load_template("skin_search");		$this->html = $OUTPUT->load_template("skin_files");		$std->updateNav(" > " . GETLANG("search"), 0);		switch ($IN["ACT"]) {			case 'search':				$this->searchForm();				break;			case 'dosearch':				$this->beginSearch();				break;		}		$OUTPUT->add_output($this->output);	}	function find($keywords)	{		global $DB;		// Create a keywords array		$keywords_array = array();		$keywords_array = explode(" ", $keywords);		// Select data query		if (!$this->searchcolumns) {			$this->searchcolumns = "*";		}		$search_data_sql = $this->table;		// Run query, assigning ref		$search_data_ref = $DB->query($search_data_sql);		// Define $search_results_array, ready for population		// with refined results		$search_results_array = array();		if ($search_data_ref) {			while ($all_data_array = $DB->fetch_row($search_data_ref)) {				// Get an entry indentifier				$my_ident = $all_data_array[$this->entry_identifier];				// Cycle each value in the product entry				foreach ($all_data_array as $entry_key => $entry_value) {					// Cycle each keyword in the keywords_array					foreach ($keywords_array as $keyword) {						// If the keyword exists...						if ($keyword) {							// Check if the entry_value contains the keyword							if (stristr($entry_value, $keyword)) {								// If it does, increment the keywords_found_[keyword] array value								// This array can also be used for relevence results								$keywords_found_array[$keyword]++;							}						} else {							// This is a fix for when a user enters a keyword with a space							// after it.  The trailing space will cause a NULL value to							// be entered into the array and will not be found.  If there							// is a NULL value, we increment the keywords_found value anyway.							$keywords_found_array[$keyword]++;						}						unset($keyword);					}					// Now we compare the value of $keywords_found against					// the number of elements in the keywords array.					// If the values do not match, then the entry does not					// contain all keywords so do not show it.					if (sizeof($keywords_found_array) == sizeof($keywords_array)) {						// If the entry contains the keywords, push the identifier onto an						// results array, then break out of the loop.  We're not searching for relevence,						// only the existence of the keywords, therefore we no longer need to continue searching						array_push($search_results_array, $all_data_array);						break;					}				}				unset($keywords_found_array);				unset($entry_key);				unset($entry_value);			}		}		$this->numresults = sizeof($search_results_array);		// Return the results array		return $search_results_array;	}	function setidentifier($entry_identifier)	{		// Set the db entry identifier		// This is the column that the user wants returned in		// their results array.  Generally this should be the		// primary key of the table.		$this->entry_identifier = $entry_identifier;	}	function settable($table)	{		// Set which table we are searching		$this->table = $table;	}	function setsearchcolumns($columns)	{		$this->searchcolumns = $columns;	}	function searchForm()	{		global $std;		$std->AssertUsingFullVersion();		$this->output .= $this->shtml->search_form();	}	function beginSearch()	{		global $DB, $IN, $CONFIG, $OUTPUT, $std, $rwdInfo;		$std->AssertUsingFullVersion();		$terms = trim($IN["terms"]);		if ($terms == "") {			$std->error(GETLANG("er_search"));			$this->searchForm();			return;		}		//$search = new mysqlSearch();		$this->settable("SELECT l.*, sym.*                         FROM dl_symlinks sym                         LEFT JOIN dl_links l ON (l.did=sym.sym_did)");		$this->setidentifier("did");		$this->setsearchcolumns("name, description, adminreview");		$searchres = $this->find($terms);		$count = count($searchres);		if ($count < 1) {			$std->warning(GETLANG("er_noresults"));			$this->searchForm();			return;		}		if (!$IN["sortvalue"])			$sortvalue = $CONFIG["default_sort"];		else			$sortvalue = $std->authSortValue($IN["sortvalue"]);		if (!$IN["order"])			$order = $CONFIG["default_order"];		else			$order = $std->authOrderValue($IN["order"]);		if (!$IN["limit"])			$limit = 0;		else			$limit = intval($IN["limit"]);		// Attempt to get custom download field data		$customFields = array();		if ($CONFIG['doCustomFields']) {			$DB->query("SELECT * FROM dl_custom");			while ($cfrow = $DB->fetch_row()) {				$index = "custom_" . $cfrow['cid'];				$customFields[$index] = $cfrow;			}		}		$pages = $std->pages(count($serachres), $CONFIG["links_per_page"], "?ACT=search");		if (count($searchres)) {			$data = array("pages" => $pages);			$this->output .= $this->shtml->search_head($data);			$domod = 0;			foreach ($searchres as $filerow) {				if ($std->canAccess($filerow['sym_catid'], "canBrowse")) {					$data = $this->parse_file_data($filerow, $customFields, 0, 0, $domod);					// TODO: CUSTOM DOWNLOAD DATA					$this->output .= $this->shtml->search_row($data);				}			}			$data = array("mod_options" => $modoptions,				"order_boxes" => $order_box,				"pages" => $pages);			$this->output .= $this->shtml->search_foot($data);		} else			$this->output .= "No files to display";	}}$loader = new mysqlSearch();?>