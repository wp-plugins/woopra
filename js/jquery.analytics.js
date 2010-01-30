jQuery(document).ready(function() {
	
	//	Exists Function
	jQuery.fn.exists = function(){return jQuery(this).length>0;}
	
	//	Variables
	var currentSuperView = null;
	var currentSubTabId = null;
	var currentSubTab = null;
	
	var pageKeys = new Array();
	
	var defaultSubTab = new Array();
	var selectedSubTabs = new Array();
	
	/**
	 * Set Current Super Tab
	 */
	function setCurrentSuperTab(id) {
		if (id == currentSuperView)
			return;
		
		//	Add current tab with 'current' class.
		jQuery("#woopra-super-tab-" + id).addClass("current");
		jQuery("#woopra-sub-tab-area-" + id).removeAttr("style");
		
		currentSuperView = id;
		
		//	We are going to process 'id'
		if (selectedSubTabs[id] == null) {
			setSubView(id, defaultSubTab[id]);
		}
	}
	
	/**
	 * Add Super Tab
	 */
	function addSuperTab(name, id) {
		
		//	Create the Tab!
		jQuery("#woopra-super-tabs").append("<li><a href='#' id='woopra-super-tab-" + id + "'>" + name + "</a></li>");
		jQuery("#woopra-analytics-box").append("<div id='woopra-sub-tab-area-" + id + "' class='woopra_analytics_inner' style='display: none;'><ul class='woopra_subtabs' id='woopra-sub-tab-ui-" + id + "'></ul><div id='woopra-viewport-" + id + "'></div></div>");
		//	Set the Action!
		jQuery("#woopra-super-tab-" + id).click(
					function() { setSuperView(id); }
				);
	}
	
	/**
	 * Add Super Sub Tab
	 */
	function addSubTab(name, id, superid, apipage) {
		
		//	Create the Sub Tab!
		jQuery("#woopra-sub-tab-ui-" + superid).append("<li><a href='#' id='woopra-sub-tab-li-" + id + "'>" + name + "</a></li>");
		//	Set the Action!
		jQuery("#woopra-sub-tab-li-" + id).click(
				function() { setSubView(superid, id); }
			);
		
		//	Add to API Key to Array
		pageKeys[superid + '-' + id] = apipage;
		
		//	If the 'defaultSubTab' tab is null for this super id, set it to the id.
		if ( defaultSubTab[superid] == null )
			defaultSubTab[superid] = id;
		
	}
	
	/**
	 * Change Super View
	 */
	function setSuperView(id) {
		
		//	If the current view is what we are at... we should not need to run?
		if (currentSuperView == id)
			return;
		
		if (currentSuperView != null) {
			//	Remove the Current Class
			jQuery('#woopra-super-tab-' + currentSuperView).removeAttr("class");
			//	Hide the Sub Tabs For this Area
			jQuery('#woopra-sub-tab-area-' + currentSuperView).attr("style", "display: none;");
			//	Clear All 'class' information from all 'li' inside 'woopra-sub-tab-area'
			jQuery('#woopra-sub-tab-area-' + currentSuperView + ' li').each(function(i) {
					jQuery(this).removeAttr("class");
				}
			);
		}
		
		//	Set the new class!
		jQuery("#woopra-super-tab-" + id).addClass("current");
		jQuery("#woopra-sub-tab-area-" + id).removeAttr("style");
		
		//	Set the current supre view to the current 'id'
		currentSuperView = id;
		
		//	Set the Sub View
		if (selectedSubTabs[id] == null)
			setSubView(id, defaultSubTab[id]);
		else
			setSubView(id, selectedSubTabs[id]);
		
		return false;
	}
	
	/**
	 * Set Sub View
	 */
	function setSubView(superid, id) {
		
		//	Set the Current Sub Tab ID
		currentSubTabId = selectedSubTabs[superid];
		
		//	If the current view is what we are at... we should not need to run?
		if (currentSubTabId == id)
			return false;

		if (currentSubTabId != null) {
			//	Remove the class.
			jQuery('#woopra-sub-tab-li' + currentSubTabId).removeAttr("class");
		}
		
		selectedSubTabs[superid] = id;
		
		//	Add class 'current'
		jQuery("#woopra-sub-tab-area-" + superid).removeAttr("style");	// doesn't matter if this is running right now.
		jQuery("#woopra-sub-tab-li-" + id).addClass("current");
		
		showWoopraAnalytics(superid, id);
		return false;
		
	}
	
	function showWoopraAnalytics(superid, id) {
		
		//	Make it a block!
		jQuery('#woopra-viewport-' + superid).attr("style", "display: block;");
		
		if ( !jQuery('#woopra-data-' + superid + '-' + id).exists() ) {
			//	Create elements!
			jQuery('#woopra-viewport-' + superid).append("<div></div>");
			//	Set the 'div' to id "#superid-id"
			jQuery('#woopra-viewport-' + superid + ' div').attr("id", "woopra-data-" + superid + "-" + id);
		}
		
		//	Put the 'Loading Image Up'
		jQuery('#woopra-data-' + superid + '-' + id).html('<p align="center"><img src="' + woopraL10n.baseurl + '/images/woopra-loader.gif" alt="' + woopraL10n.loading + '" /><br/>' + woopraL10n.loading + '</p>');
		
		//	Request the Data
		requestData(superid + '-' + id, pageKeys[superid + '-' + id]);
	}
	

	function requestData(area, key) {
		
		//	Get the data requested!
		jQuery.get(
			woopraL10n.siteurl + '/wp-admin/admin-ajax.php',	//	The admin ajax file
			{ 
				action: "woopra",
				datatype: "regular",
				apikey: woopraL10n.apikey,
				wkey: escape(key),
				date_format: woopraL10n.dateformat,
				from: jQuery('#woopra_from').val(),
				to: jQuery('#woopra_to').val()
			},
			function(returned_data) {
				if (returned_data != null)
					jQuery('#woopra-data-' + area).html(returned_data);
				else
					jQuery('#woopra-data-' + area).html(woopraL10n.error);
			}
		);
		
	}
	
	//	Create Super Tabs
	addSuperTab(woopraL10n.visitors,	'visitors');
	addSuperTab(woopraL10n.systems,		'systems');
	addSuperTab(woopraL10n.pages,		'pages');
	addSuperTab(woopraL10n.referrers,	'referrers');
	addSuperTab(woopraL10n.searches,	'searches');
	addSuperTab(woopraL10n.tagvisitors,	'tagvisitors');
	
	//	Create Visitors Sub Tabs
	addSubTab( 'Overview',				'overview',		'visitors',	'GLOBALS' );
	addSubTab( 'Countries',				'countries',	'visitors',	'COUNTRIES' );
	addSubTab( 'Bounce Rate',			'bounces',		'visitors',	'VISITBOUNCES' );
	addSubTab( 'Visit Durations',		'durations',	'visitors',	'VISITDURATIONS' );
	
	//	Create Systems Sub Tabs
	addSubTab( 'Browsers',				'browsers',		'systems',	'BROWSERS' );
	addSubTab( 'Platforms',				'platforms',	'systems',	'PLATFORMS' );
	addSubTab( 'Screen Resolutions',	'resolutions',	'systems',	'RESOLUTIONS' );
	addSubTab( 'Languages',				'languages',	'systems',	'LANGUAGES' );

	//	By Default Set The Current View
	//	@todo Make this confirgurable!
	setCurrentSuperTab('visitors');
	
	
});

/************************ 
 * 
 * I am changing this code code.
 * 
 ************************/

var woopra_website;

date = new Date();
var date_to = getDateText(date);
date.setDate(date.getDate()-30);
var date_from = getDateText(date);
var date_format = woopraL10n.dateformat;

function initDatePicker() {
	document.getElementById('woopra_from').value = date_from;
	document.getElementById('woopra_to').value = date_to;
}

function expandByDay(key, hashid, id, index) {
	var row = document.getElementById('wlc-' + hashid + '-' + id);
	if (row.style.display == 'table-row') {
		row.style.display = 'none';
	}
	else {
		row.style.display = 'table-row';
	}
	
	if (row.className == 'loaded') {
		return false;
	}
	row.className = 'loaded';
	
	var phpfile = woopraL10n.siteurl + '/wp-admin/admin-ajax.php?action=woopra&datatype=flash&apikey=' + woopradefaultL10n.apikey + '&id=' + index + '&wkey=' + key + '&date_format=' + date_format + '&from=' + date_from + '&to=' + date_to;
	var so = new SWFObject(woopraL10n.baseurl + "/flash/open-flash-chart.swf", hashid, "968", "110", "9");
	so.addVariable("data-file", escape(phpfile));
	so.addParam("allowScriptAccess", "sameDomain");
	so.addParam("wmode", "transparent");
	so.addParam("bgcolor", "#FFFFFF");

	so.write('linecharttd-' + hashid + '-' + id);
	return false;
}

function expandReferrer(key, hashid) {
	trref = document.getElementById('refexp-'+hashid);
	if (trref.style.display == 'none') {
		trref.style.display = 'table-row';
	}
	else {
		trref.style.display = 'none';
	}
	if (trref.className == 'loaded') { return false; }
	
	trref.className = 'loaded';
	
	tdref = document.getElementById('refexptd-' + hashid);
	setPageLoading('refexptd-' + hashid);
	requestData('refexptd-' + hashid, key);
	return false;
}

function showDatePicker() {
	initDatePicker();
	dp = document.getElementById("datepickerdiv");
	dp.style.display = 'block';
	return false;
}

function closeDatePicker() {
	dp = document.getElementById("datepickerdiv");
	dp.style.display = 'none';
	return false;
}

function applyDatePicker() {
	date_from = document.getElementById('woopra_from').value;
	date_to = document.getElementById('woopra_to').value;
	//pageObjects = new Array();
	refreshDateLinkText();
	refreshCurrent();
	closeDatePicker();
	return false;
}

function getDateText(date) {
	text = date.getFullYear() + '-';
	m = date.getMonth() + 1;
	if (m < 10) { text += '0'; }
	text += m + '-';
	d = date.getDate();
	if (d <10) { text += '0'; }
	text += d;
	return text;
}

function getDateLinkText() {
	return '<strong>From:</strong> ' + date_from + ' <strong>To:</strong> ' + date_to;
}

function refreshDateLinkText() {
	document.getElementById("daterange").innerHTML = getDateLinkText();
}

function refreshCurrent() {
	superid = currentSuperView;
	subid = selectedSubTabs[currentSuperView];
	pageObjects[superid + '-' + subid] == null;
	pageid = superid + '-' + subid;
	setPageLoading(pageid);
	requestData(pageid, pageKeys[pageid]);
	return false;
}