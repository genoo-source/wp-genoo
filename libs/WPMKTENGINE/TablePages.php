<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General Public License Ver. 2 (GPL)
 *  Licensed "As-Is"; all warranties are disclaimed.
 *  HTML: http://www.gnu.org/copyleft/gpl.html
 *  Text: http://www.gnu.org/copyleft/gpl.txt
 *
 * Proprietary Licensing:
 *  Remaining code elements, including without limitation:
 *  images, cascading style sheets, and JavaScript elements
 *  are licensed under restricted license.
 *  http://www.wpmktgengine.com/terms-of-service
 *  Copyright 2016 Genoo LLC. All rights reserved worldwide.
 */

namespace WPMKTENGINE;

use WPMKTENGINE\Wordpress\Utils;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Wordpress\Notice;
use WPMKTENGINE\Tools;
use WPMKTENGINE\RepositoryLandingPages;
use WPMKTENGINE\RepositoryPages;

class TablePages extends Table
{
    /** @var \WPMKTENGINE\RepositoryPages */
    var $repositoryPages;
    /** @var \WPMKTENGINE\RepositorySettings */
    var $repositorySettings;
    /** @var string */
    var $activeForm;
    /** @var bool */
    var $set = FALSE;
    /** @var string */
    var $folderHtml = '';

    const COMMAND_NEW_FOLDER = 'wpme_new_folder_to_create_command';

    /**
     * Constructor
     *
     * @param RepositoryLog $log
     */

    function __construct(\WPMKTENGINE\RepositoryPages $repositoryPages, \WPME\RepositorySettingsFactory $repositorySettings)
    {
        global $status, $page;
        $this->repositoryPages = $repositoryPages;
        $this->repositorySettings = $repositorySettings;
        parent::__construct();
    }


    /**
     * Basic setup, returns table columns.
     *
     * @return array
     */

    function get_columns()
    {
        return array(
            'name' => __('Template name', 'wpmktengine'),
            'landing' => 'Used by URL\'s',
        );
    }


    /**
     * Basic setup, returns sortable columns
     *
     * @return array
     */

    function get_sortable_columns(){ 
      return array(); 
    }

    public function get_delete_link($id){
      $nonce = wp_nonce_url(
        get_admin_url() .'post.php?post=' . $id . '&amp;action=delete', 
        'delete-post_'.$id
      );
      return "<a href=\"$nonce\"><span class=\"dashicons dashicons-trash\"></span></a>";
    }

    public function generate_activate_link($active, $post){
      $realUrlEmpty = strtok(Utils::getRealUrl(), "?");
      $realUrl = $realUrlEmpty . "?page=WPMKTENGINEPages";
      $linkToken = $active ? 'genooDisableLandingHomepage' : 'genooMakeLandingHomepage';
      $linkText = $active ? 'Disable homepage' : 'Activate Homepage';
      $link = Utils::addQueryParam($realUrl, $linkToken, $post->ID);
      return '&nbsp;|&nbsp;<a href="'. $link .'">'. __($linkText, 'wpmktengine') .'</a>';
    }

    public function column_landing($item)
    {
        if($this->isFolder($item)){
          return '';
        }
        if(!empty($item['landing'])){
            $id = "hidden-list-" . $item['id'];
            $r = "<table class=\"wp-list-table widefat\" id=\"$id\">";
            $r .= "<thead><tr>";
                $r .= "<th class=\"manage-column column-title\" scope=\"col\">Title</th>";
                $r .= "<th class=\"manage-column column-url\" scope=\"col\">Url</th>";
                $r .= "<th class=\"manage-column column-setup\" scope=\"col\">Delete</th>";
                $r .= "<th class=\"manage-column column-active\" scope=\"col\">Active</th>";
                $r .= "<th class=\"manage-column column-home\" scope=\"col\">Homepage</th>";
                $r .= "<th class=\"manage-column column-redirect\" scope=\"col\">Redirect</th>";
            $r .= "</tr></thead>";
            $r .= "<tbody>";
            $counterHide = false;
            $counterMax = 5;
            $counter = 1;
            $counterRemaing = count($item['landing']) > $counterMax ? (count($item['landing']) - $counterMax) : 0;
            $counterJS = "onclick='Api.prolognedList(this, event, \"$id\");'";
            // wpme-landing-pages
            foreach($item['landing'] as $post){
                if($counter > $counterMax){
                    $counterHide = true;
                }
                $link = admin_url('post.php?post='. $post->ID .'&action=edit');
                $class = $counter > $counterMax ? "class='next hidden'" : "\"class='next'";
                $r .= "<tr $class>";
                $title = empty($post->post_title) ? "No Title." : $post->post_title;
                $r .= "<td><a href=\"". $link ."\">". $title ."</a></td>";

                // URL
                $metaURL = get_post_meta($post->ID, 'wpmktengine_landing_url', true);
                $r .= "<td>". RepositoryLandingPages::base() . $metaURL .  "</td>";

                // DELETE
                $r .= "<td>" . $this->get_delete_link($post->ID) . "</td>";

                // ACTIVE
                $metaActive = get_post_meta($post->ID, 'wpmktengine_landing_active', true);
                if ($metaActive == 'true') {
                    $metaActive = '<spain class="genooTick active">&nbsp;</spain>';
                } else {
                    $metaActive = '<span class="genooCross">&times;</span>';
                }
                $r .= "<td>". $metaActive ."</td>";
                // HOMEPAGE
                $metaActiveHome = get_post_meta($post->ID, 'wpmktengine_landing_homepage', true);
                $metaActiveHome = $metaActiveHome === 'true';
                $r .= "<td>";
                if($metaActiveHome){
                  $r .= "<span class=\"genooTick active\">&times;</span>";
                } else {
                  $r .= "<span class=\"genooCross\">&times;</span>";
                }
                $r .= $this->generate_activate_link($metaActiveHome, $post);
                $r .= "</td>";
                // REDIRECT
                $metaUrlActive = get_post_meta($post->ID, 'wpmktengine_landing_redirect_active', true);
                $metaUrl = get_post_meta($post->ID, 'wpmktengine_landing_redirect_url', true);
                if ($metaUrlActive == 'true') {
                    $metaREDIRECT = '<span class="genooTick active">&nbsp;</span>';
                    $metaREDIRECT .= '<br />Redirects to: <strong>'. $metaUrl  .'</strong>';
                } else {
                    $metaREDIRECT = '<span class="genooCross">&times;</span>';
                }
                $r .= "<td>$metaREDIRECT</td>";
                $r .= "</tr>";
                $counter++;
            }
            $r .= '</tbody>';
            $r .= '</table>';
            if($counterHide){
                $r .= "<a class='button' $counterJS href=\"#\"><span>Show</span> Remaining ($counterRemaing)</a>";
            }
            return $r;
        }
        return __('No URL\'s at this site are using this template.', 'wpmktengine');
    }

    public function get_column_name($item){
      return isset($item[\WPMKTENGINE\RepositoryPages::REPO_SORT_NAME]) 
        && !empty($item[\WPMKTENGINE\RepositoryPages::REPO_SORT_NAME]) 
        && $item[\WPMKTENGINE\RepositoryPages::REPO_SORT_NAME] !== 'undefined' 
          ? $item[\WPMKTENGINE\RepositoryPages::REPO_SORT_NAME] 
          : __('No title.', 'wpmktengine');      
    }
    
    /**
     * This happens only once and then each row
     * changes the id value with simple `str_replace`
     */
    private function make_folder_html(){
      $folderStructure = $GLOBALS[\WPMKTENGINE\RepositoryPages::FOLDER_STRUCTURE];
      $realUrlEmpty = strtok(Utils::getRealUrl(), "?");
      $realUrl = $realUrlEmpty . "?page=WPMKTENGINEPages";
      $appendedUrl = Utils::addQueryParams($realUrl, array(
        'genooPagesRename' => '%%ID%%',
        'genooPagesRenameTitle' => '%%NEW_NAME%%'
      ));
      // Text values
      $textNewFolder = __('Create a new folder &rarr;', 'wpmktengine');
      $textNewPage = __('Move page to a folder:', 'wpmktengine');
      $textSelect = __('Select a folder:', 'wpmktengine');
      $textSelectNewFolder = __('Create a new folder:', 'wpmktengine');
      $textSelectNewFolderPlaceholder = __('New folder name', 'wpmktengine');
      $returnedHtml = '';          
      // Generate HTML
      $returnedHtml .= "<div class=\"wpme_page_select_form\">";
      $returnedHtml .= "<form 
        data-id=\"%%ID%%\"
        data-name=\"%%NAME%%\"
        data-url=\"$appendedUrl\"
        onchange=\"Genoo.onPageMove(event);\" 
        onsubmit=\"Genoo.onPageMove(event);\" 
        style=\"display: inline; margin: 0\" 
        method=\"POST\" 
        action=\"$realUrl\">
      ";
      $returnedHtml .= "<input type=\"hidden\" style=\"display: none\" name=\"id\" value=\"%%ID%%\" />";
      $returnedHtml .= "<h3>$textNewPage</h3>";
      $returnedHtml .= "<h4>Page name: %%NAME%%</h4>";
      $returnedHtml .= "<label>$textSelect <br /><select id=\"wpme_page_select_folder_%%ID%%\" name=\"wpme_page_select_folder\">";
      foreach($folderStructure as $key => $value){
        $returnedHtml .= "<option value=\"$key\">$value</option>";
      }
      // Form Guts
      $returnedHtml .= "<option value=\"". self::COMMAND_NEW_FOLDER ."\">$textNewFolder</option>";
      // End HTML
      $returnedHtml .= "</select></label><br /><br />";
      $returnedHtml .= "<div id=\"wpme_page_select_new_folder_%%ID%%\" class=\"hidden\">";
        $returnedHtml .= "<label>$textSelectNewFolderPlaceholder: <br /><small>(only numbers and letters allowed)</small><br />";
        $returnedHtml .= "
      <input 
          id=\"wpme_page_select_new_folder_input_%%ID%%\" 
          type=\"text\" 
          placeholder=\"$textSelectNewFolderPlaceholder\" 
          name=\"wpme_page_select_new_folder\" 
          pattern=\"[a-zA-Z0-9 -]+\"
          />
      </label>
      ";
      $returnedHtml .= "</div>";
      $returnedHtml .= "<p><input type=\"submit\" id=\"submit\" class=\"button button-primary\" value=\"Change\"></p>";
      $returnedHtml .= "</form></div>";
      // Save generated HTML
      $this->folderHtml = $returnedHtml;
    }

    public function get_folder_html($name, $id){
      return "<div id=\"wpme_move_page_$id\" style=\"display:none\">" . str_replace(
        array('%%ID%%', '%%NAME%%'),
        array($id, $name),
        $this->folderHtml
      ) . "</div>";
    }

    public static function get_row_id($name){
      $nameOriginal = RepositoryPages::removeUniqueName($name);
      $unique = RepositoryPages::extractUniqueName($name);
      return 'row-' . md5($nameOriginal) . $unique;
    }

    /**
     * @param $item
     * @return string
     */
    public function column_name($item, $nesting = null)
    {
        $name = $this->get_column_name($item);
        $rowId = self::get_row_id($name);
        $name = RepositoryPages::removeUniqueName($name);
        if($this->isFolder($item)){
           return "
            <span id=\"$rowId\"></span>
            <a class=\"wpme-folder-switch\" href=\"#\" onclick=\"Genoo.onPageCollapse(event);\">
              <span class=\"dashicons dashicons-plus\"></span>
              <span class=\"dashicons dashicons-minus\"></span>
              <span class=\"dashicons dashicons-category\"></span> $name
            </a>
           ";
        }
        if($this->isDrafts($item)){
           return "
            <span id=\"$rowId\"></span>
            <span class=\"dashicons dashicons-category\"></span> $name
           ";
        }
        $actions = $this->row_actions(array(
            'edit' => $this->getLink('edit', $item['id']),
            'create' => $this->getLink('create', $item['id']),
            'prev' => $this->getLink('prev', $item['id']),
            'rename' => $this->getLink('rename', $item['id'], $item['name']),
            'move' => $this->getLink('move', $item['id'], $item['name']),
            'trash' => $this->getLink('trash', $item['id'])
        ));
        $actionsBublished = $this->row_actions(
          array(
            'id' => 'ID: ' . $item['id'],
            'published' => __('Published: ', 'wpmktengine') . date('Y/m/d', strtotime($item['created'])),
          )
        );
        // Nesting DIV
        $actionDiv = $nesting === null ? "<div>" : "<div class=\"nested level-$nesting\">";
        $actionDivClosing = "</div>";
        return
          "<span id=\"$rowId\"></span>"
          .  $actionDiv 
          . $name
          . $actionsBublished 
          . $actions 
          . $actionDivClosing 
          . $this->get_folder_html($name, $item['id']);
    }


    public function getNewLandingPageLink($id = false){
      $realUrlEmptyAdmin = rtrim(admin_url(), '/') . '/';
      return Utils::addQueryParams($realUrlEmptyAdmin . 'post-new.php', array(
        'post_type' => 'wpme-landing-pages',
        'wpmktengine_landing_template' => $id,
      ));
    }

    public function isDrafts($item){
      return isset($item['isDrafts']);
    }

    public function getFirstItem(){
      $drafts = __('Landing Pages Without a Page Template', 'wpmktengine');
      $isSearch = $this->searchQuery !== '';
      $draftsData = RepositoryLandingPages::findDrafts();
      if(empty($draftsData)){
        return null;
      }
      return array(
        \WPMKTENGINE\RepositoryPages::REPO_SORT_NAME => $drafts,
        'name' => $drafts,
        'isDrafts' => true,
        'className' => $isSearch ? '' : 'highlight',
        'id' => null,
        'craeted' => null,
        'landing' => $draftsData,
      );
    }

    /**
     * Get Link
     *
     * @param $which
     * @param null $id
     * @param null $name
     * @return string
     */
    function getLink($which, $id = NULL, $name = NULL)
    {
        $r = new \stdClass();
        // Get url without params
        $realUrlEmpty = strtok(Utils::getRealUrl(), "?");
        $realUrl = $realUrlEmpty . "?page=WPMKTENGINEPages";
        $r->href = '';
        $r->other = '';
        $r->title = '';
        switch($which){
            case 'edit':
                $r->href =  Utils::addQueryParams(WPMKTENGINE_BUILDER_NEW, array(
                  'id' => $id,
                ));
                $r->title = 'Edit';
                $r->other = 'target="_blank"';
                break;
            case 'create':
                $r->href = $this->getNewLandingPageLink($id);
                $r->title = 'Create a URL';
                break;
            case 'prev':
                $r->href = Utils::addQueryParam(WPMKTENGINE_HOME_URL, 'genooIframeBuidler', $id);
                $r->title = 'Preview';
                $r->other = 'target="_blank"';
                break;
            case 'rename':
                $r->href = '#';
                $r->title = 'Rename';
                $title = __('How would you like rename this Page Template?.', 'wpmktengine');
                $url = Utils::addQueryParams($realUrl, array(
                    'genooPagesRename' => $id,
                    'genooPagesRenameTitle' => ''
                ));
                // Convert the value and remove first and last characters
                $value = addslashes($name);
                $r->other = 'onclick="Tool.promptToRename(\''. $title .'\', \''. $url .'=\', \''. $value .'\');"';
                break;
            case 'move':
                $r->href = '#TB_inline?width=100%&height=100%&inlineId=wpme_move_page_' . $id;
                $r->title = __('Move to a folder', 'wpmktengine');
                // Convert the value and remove first and last characters
                $r->other = 'class="thickbox"';
                break;
            case 'trash':
                $r->href = Utils::addQueryParams($realUrl, array(
                    'genooPagesDelete' => $id
                ));
                $r->other = 'onclick="Tool.promptBeforeGo(event, this, \'Are you sure you want to delete this Layout Page?\');"';
                $r->title = 'Delete';
                break;
        }
        return '<a href="'. $r->href .'" '. $r->other .'>'. $r->title .'</a>';
    }

    public function get_table_id(){
      return 'wpme-landing-pages';
    }

    /**
     * Get a list of CSS classes for the list table table tag.
     *
     * @since 3.1.0
     * @access protected
     *
     * @return array List of CSS classes for the table tag.
     */
    protected function get_table_classes() {
        return array( 'widefat', $this->_args['plural'] );
    }

    /**
     * Remove cached forms
     *
     * @param $which
     */

    function extra_tablenav($which)
    {
        $where = strtok(Utils::getRealUrl(), "&");
        $whereNewLandingPage = $this->getNewLandingPageLink();
        $searchText =  __('Search Pages', 'wpmktengine');
        if($which == 'top'){
            echo '
              <div class="alignleft actions">
                <form style="display: inline; margin: 0" method="POST" action="'. $where .'">
                  <input type="submit" name="genooPagesFlushCache" id="submit" class="button alignCenter genooExtraNav" value="'. __('Sync Templates', 'wpmktengine') .'">
                  <a target="_blank" class="button button-primary genooExtraNav" href="'. WPMKTENGINE_BUILDER_NEW .'">'. __('Add new Page Template', 'wpmktengine') .'</a>
                  <a href="'. $whereNewLandingPage .'" class="button button-primary genooExtraNav">'. __('Add new URL', 'wpmktengine') .'</a>
                </form>
              </div>
            ';
            echo "
              <script type=\"text/javascript\">
                var Genoo = Genoo || {};
                
                /**
                 * Rid the new page name of wrong characters
                 */
                Genoo.sanatizeFolderName = function(newFolder){
                  return newFolder.trim();
                };

                /**
                 * Rename a page using method already in
                 */ 
                Genoo.renamePageTo = function(name, url){
                  window.location = url.replace('%%NEW_NAME%%', encodeURIComponent(name));
                };

                /**
                 * On Page Move to a new / or existing folder 
                 */
                Genoo.onPageMove = function(event){
                  try {
                    // Get basic vars
                    var eventType = event.type;
                    var eventForm = eventType === 'change' ? event.target.parentNode.parentNode : event.target;
                    var pageId = eventForm.getAttribute('data-id');
                    var pageName = eventForm.getAttribute('data-name');
                    var pageUrl = eventForm.getAttribute('data-url');
                    var eventHiddenSpot = document.getElementById('wpme_page_select_new_folder_' + pageId);
                    // Magic
                    var valueForFolder = document.getElementById('wpme_page_select_folder_' + pageId).value;
                    var valueForNewFolder = document.getElementById('wpme_page_select_new_folder_input_' + pageId).value;
                    // If we are swapping pages and land on a new folder
                    if(eventType === 'change'){
                      if(event.target.value === '". self::COMMAND_NEW_FOLDER ."'){
                        Tool.removeClass(eventHiddenSpot, 'hidden');
                      } else {
                        Tool.addClass(eventHiddenSpot, 'hidden');
                      }
                    } else {
                      // Deciding factor, if we move to a new folder
                      var movingToNewFolder = valueForFolder === '". self::COMMAND_NEW_FOLDER ."';
                      var newPageName = movingToNewFolder
                        ? Genoo.sanatizeFolderName(valueForNewFolder) + ' / ' + pageName
                        : valueForFolder + pageName;
                      event.preventDefault();
                      // Cool lets rename this page
                      Genoo.renamePageTo(newPageName, pageUrl);
                    }
                  } catch(errror){
                    // Not really
                  }
                };

                Genoo.getElementLevel = function(element){
                  return element ? parseInt(element.getAttribute('data-level'), 10) : 0;
                };

                Genoo.isCollapsed = function(element){
                  var lower = Tool.hasClass(element, 'collapsed');
                  var lowerUpper = Tool.hasClass(element.parentNode, 'collapsed');
                  return lower || lowerUpper;
                };

                Genoo.rowCollapsing = function(element, className){
                  // Is collapsed?
                  window.localStorage.setItem(element, className);
                };

                /**
                 * On Page Move to a new / or existing folder 
                 */
                Genoo.onPageCollapse = function(event, eventId){
                  event.preventDefault();
                  // The row above
                  var spanId = event.currentTarget.previousSibling.previousSibling.getAttribute('id');
                  var spanIdMapped = spanId;
                  var closeDownArray = folderDependencies[spanIdMapped];
                  var parentRow = event.currentTarget.parentNode.parentNode;
                  var isCollapsed = Genoo.isCollapsed(event.currentTarget.parentNode);
                  // Collapse
                  if(isCollapsed){
                    Tool.removeClass(parentRow, 'collapsed');
                    Genoo.rowCollapsing(spanIdMapped, '');
                  } else {
                    Tool.addClass(parentRow, 'collapsed');
                    Genoo.rowCollapsing(spanIdMapped, 'collapsed');
                  }
                  // Collapse
                  // Iterate over array and close or open
                  var i = 0;
                  while (closeDownArray[i]) {
                    var almostRowId = closeDownArray[i];
                    var element = document.getElementById(almostRowId);
                    var closableTr = element.parentNode.parentNode;
                    if(isCollapsed){
                      Tool.removeClass(closableTr, 'hidden');
                      Genoo.rowCollapsing(almostRowId, '');
                    } else {
                      Tool.addClass(closableTr, 'hidden');
                      Genoo.rowCollapsing(almostRowId, 'hidden');
                    }
                    i++;
                  }
                };
              </script>
            ";
            $this->search_box(
              $searchText, 
              __('Page name, URL or ID', 'wpmktengine'), 
              'search-wpme-landing-pages'
            );
        }
        if($which == 'bottom'){
          echo "
            <script type=\"text/javascript\">
              // Get all folders
              var folders = document.querySelectorAll('#wpme-landing-pages .wpme-folder-switch');
              // Hide, show and attach handlers
              var folderDependencies = JSON.parse('" . json_encode($GLOBALS[\WPMKTENGINE\RepositoryPages::FOLDER_JS_STRUCTURE]) . "');
              // Figure out if open or close, close on default
              var foldersToCollapse = document.querySelectorAll('#wpme-landing-pages .nested');
              for (i = 0; i < foldersToCollapse.length; ++i) {
                var folderToCollapse = foldersToCollapse[i].parentNode;
                if(!Tool.hasClass(folderToCollapse, 'hidden')){
                  Tool.addClass(folderToCollapse, 'hidden');
                }
              }
              for (i = 0; i < folders.length; ++i) {
                var level = parseInt(folders[i].parentNode.getAttribute('data-level'), 10);
                var folderToCollapse = folders[i].parentNode.parentNode;
                if(!Tool.hasClass(folderToCollapse, 'collapsed') && level === 0){
                  Tool.addClass(folderToCollapse, 'collapsed');
                }
              }
            </script>
          ";
        }
    }

    /**
     * No Items notices
     */
    function no_items(){ echo __('There are no Pages created in your account.', 'wpmktengine'); }

    /**
     * Process it!
     */
    public function process()
    {
        // process actions
        if(isset($_POST['genooPagesFlushCache'])){
            try{
                $this->repositoryPages->flush();
                $this->addNotice('updated', __('All pages successfully synced.', 'wpmktengine'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
        if(isset($_GET['genooPagesDelete'])){
            // Template id
            $template_id = $_GET['genooPagesDelete'];
            // Prepare
            $this->prepare_items();
            // Go through this
            $template_checks = $this->items;
            $template_checks_found = NULL;
            if(Utils::isIterable($template_checks)){
                foreach($template_checks as $item){
                    if(!is_array($item)){
                      continue;
                    }
                    if(array_key_exists('id', $item) && $item['id'] == $template_id && !empty($item['landing'])){
                        $template_checks_found = $item['landing'];
                        break;
                    }
                }
            }
            if(!is_null($template_checks_found)){
                // We are not deleting, page has dependencies
                $landing = '<ol>';
                    foreach($template_checks_found as $template){
                        $landing .= '<li>'. $template->post_title .'</li>';
                    }
                $landing .= '</ol>';
                $landingAfter = '</p><p>If you wish to remove the template, please remove the Landing Pages first.';
                $this->addNotice('error', __('The template could not be removed. Because these Landing pages depend on it: ' . $landing . $landingAfter, 'wpmktengine'));
                return;
            }
            // Delete page
            try {
                $this->repositoryPages->deletePage($template_id);
                $this->repositoryPages->flush();
                $this->addNotice('updated', __('Template successfully removed.', 'wpmktengine'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
        // Rename page
        if(isset($_GET) && array_key_exists('genooPagesRename', $_GET) && array_key_exists('genooPagesRenameTitle', $_GET)){
            // If all parameters present
            if(!empty($_GET['genooPagesRename']) && !empty($_GET['genooPagesRenameTitle'])){
                $id = $_GET['genooPagesRename'];
                $name = $_GET['genooPagesRenameTitle'];
                try {
                    $this->repositoryPages->renamePage($id, $name);
                    $this->repositoryPages->flush();
                    $this->addNotice('updated', __('Template successfully renamed.', 'wpmktengine'));
                } catch (\Exception $e){
                    if($e->getCode() === 1000){
                      $this->addNotice('updated', __('Template successfully renamed.', 'wpmktengine'));
                      return;
                    }
                    $this->addNotice('error', $e->getMessage());
                }
            }
        }
    }

    /**
     *  Prepares, sorts, delets, all that stuff :)
     */
    public function prepare_items()
    {
        if($this->set == TRUE){ return; }
        try {
            // Get data
            $perPage = 500;
            // Get all pages
            $allLogs = $this->repositoryPages->getStructuredPagesTable($this->searchQuery);
            // Generate move page to a folder html
            $this->make_folder_html();
            // Setup data
            $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
            // Sort
            $_GET['orderby'] = 'id';
            // Sort - folders to top (alpha)
            usort($allLogs, function($a, $b){
              if($this->isFolder($a) && !$this->isFolder($b)){
                $name = $this->get_column_name($a);
                $name = RepositoryPages::removeUniqueName($name);
                // strcmp
                return -1;
              }
              // Both folders? Alpha
              if($this->isFolder($a) && $this->isFolder($b)){
                $nameA = $this->get_column_name($a);
                $nameA = RepositoryPages::removeUniqueName($nameA);
                $nameB = $this->get_column_name($b);
                $nameB = RepositoryPages::removeUniqueName($nameB);
                $result = strcmp($nameA, $nameB);
                return $result;
              }
              // No folders, just plain website
              return 1;
            });
            // Paginate
            $this->found_data = array_slice($allLogs,(($this->get_pagenum()-1)* $perPage), $perPage);
            $this->set_pagination_args(array('total_items' => count($allLogs), 'per_page' => $perPage));
            // Append drafts row
            $firstItem = $this->getFirstItem();
            if($firstItem){
              array_unshift($this->found_data, $firstItem);
            }
            $this->items = $this->found_data;
            $this->set = TRUE;
        } catch (\WPMKTENGINE\ApiException $e){
            $this->addNotice('error', 'Genoo API: ' . $e->getMessage());
        } catch (\Exception $e){
            $this->addNotice('error', $e->getMessage());
        }
    }
}
