<?	
class reporter
{
	public $query;
	public $columns = array(array('name'=>'','width'=>'','dataType'=>'','color'=>'','align'=>'','background'=>'','content'=>''));
	public $width = 21;
	public $height = 29;
	public $margin = array('top'=>'1','left'=>'1','bottom'=>'1','right'=>'1');
	public $headHeigh = 5;
	public $footHeigh = 2;
	public $pageRows = 40;
	public $template = '';
	public $templateData = array(array('key'=>'value'));
	public $font = 'arial';
	public $fontSize = '12';
	public $fontWeight = 'bold';
	public $fontStyle = 'italic';
	public $headFont = 'arial';
	public $headFontWeight = 'bold';
	public $headFontStyle = 'italic';
	public $headFontSize = 14;
	public $headGgColor = '#EAEAEA';
	public $style;
	public $pages;
	public $dpc = 38;
	public $dataHeight;
	function __construct($query = '',$columns = array(),$width = array(),$height = 0,$margin = 0,$pageRows = 0,$template = '',$templateData = array()){
		$this->query = $query;
		if(is_array($columns) && count($columns) > 0){
			$this->columns = $columns;
		}
		if($width){
			$this->width = $width;
		}
		if($height){
			$this->height = $height;
		}
		if(is_array($margin) && count($margin) > 0){
			$this->margin = $margin;
		}
		if($pageRows){
			$this->pageRows = $pageRows;
		}
		if($template){
			$this->template = $template;
		}
		if(is_array($templateData) && count($templateData) > 0){
			$this->templateData = $templateData;
		}
	}
	function arStringLength($str) 
	{
	  if(mb_detect_encoding($str) == 'UTF-8') {
	      $str = utf8_decode($str);
	  }
	  return strlen($str);
	}
	
	function getLen($str){
		$len = 0;
		$warray = explode(" ",$str);
		foreach($warray as $w){
			$len+=self::arStringLength($w);
		}
		return $len;
	}
	function generateTemplate(){
		$template = $this->template;
		ob_start();
		include($this->template);
		$template = ob_get_contents();
		ob_end_clean();	
		$width = $this->width - ($this->margin['left'] + $this->margin['right']);
		$height = $this->height - ($this->margin['top'] + $this->margin['bottom']);
		$this->dataHeight = $height - ($this->headHeigh + $this->footHeigh + 1);
		$this->style = '
		@media print {
			  .page {}
		}
		.page{
			width:'.$width.'cm;
			height:'.$height.'cm;
			overflow:hidden;
  			margin-top:0px;
 			margin-bottom:0px;
 			padding:0px;
 			page-break-after: always;
 			background-color:#ffffff;
 			display:none;
		}
		.pageTable
		{
			width:'.$width.'cm;
			height:'.$height.'cm;
			border-spacing: 0;
			border-collapse: collapse;
		} 
		.pageTableHead{
			height:'.$this->headHeigh.'cm;
		}
			.pageTableFoot{
			height:'.$this->footHeigh.'cm;
		} 
		.pageData{
			height:'.$this->dataHeight.'cm;
		}
		.dataContaner
		{
			height:'.$this->dataHeight.'cm;
			overflow:hidden;
		}';
		foreach($this->templateData as $key=>$value){
			$template = str_replace('<'.$key.'>',$value,$template);
		}
		$this->template = $template;
	}
	
	function createPage($pageData,$pageCount){
		$dataTable = '';
		$dataTable.='<div class="dataContaner">';
		$dataTable.= self::createTable($pageData);
		$dataTable.='</div>';
		$template = $this->template;
		$template = str_replace("<reportDate>",date(Y).'-'.date(m).'-'.date(d),$template);
		$template = str_replace("<pageNumber>",$pageCount,$template);
		$template = str_replace("<pagesCount>",count($this->pages),$template);
		$template = str_replace("<data>",$dataTable,$template);
		$page = '<div class="page">'.$template.'</div>';
		return $page;
	}
	
	function createReport($options = array()){
		if(is_array($options) && count($options) > 0){
			$this->width = $options['PaperWidth'];
			$this->height = $options['PaperHeight'];
			$this->headHeigh = $options['HeaderHeight'];
			$this->footHeigh = $options['FooterHeight'];
			$this->pageRows = $options['rows'];
			$this->font = $options['Font'];
			$this->fontSize = $options['FontSize'];
			$this->fontWeight = $options['fontWeight'];
			$this->fontStyle = $options['fontStyle'];
			$this->headFont = $options['HeadFont'];
			$this->headFontWeight = $options['HeadfontWeight'];
			$this->headFontStyle = $options['headfontStyle'];
			$this->headFontSize = $options['HeadFontSize'];
			$this->headGgColor = $options['HeadBg'];
			$numberOfColumns = count($options['name']);
			for($x = 0; $x < $numberOfColumns; $x++){
				$this->columns[$x]['name'] = $options['name'][$x];
				$this->columns[$x]['width'] = $options['width'][$x];
				$this->columns[$x]['color'] = $options['color'][$x];
				$this->columns[$x]['align'] = $options['align'][$x];
				$this->columns[$x]['background'] = $options['bgcolor'][$x];
			}			
		}
		self::generateTemplate();
		$result = query_result($this->query);
		$this->pages = array_chunk($result, $this->pageRows);
		$pages = array();
		$pages[0] = array();
		$pagesCounter = 0;
		$pageContent = 0;
		$charHeight = ($this->fontSize+2);
		$charWidth = $this->fontSize;
		$contentSize = $this->dataHeight;
		foreach($result as $row){
			$maxHeight = 1;
			foreach($this->columns as $column){
				$colWidth = ($column['width'] * $this->dpc)-2;
				$colLen = self::getLen(trim($row[$column['content']]));
				$colActWidth = (($colLen * $charWidth) / 2);
				$colHeight = ceil($colActWidth / $colWidth);
				if($colHeight > $maxHeight){
					$maxHeight = $colHeight;
				}
			}
			$pageContent = $pageContent + $maxHeight;
			if($pageContent > $this->pageRows){
				$pagesCounter++;
				$pageContent = $maxHeight;
				$pages[$pagesCounter] = array();
			}
			//$row['tax_pace'] = $maxHeight;
			array_push($pages[$pagesCounter],$row);
		}
		$this->pages = $pages;
		$count = 1;
		$report = '';
		foreach($this->pages as $page){
			$report.= self::createPage($page,$count);
			$count++;
		}
		return self::reporterInterface($report);
	}
	
	function createTable($data){
		$table = '<table cellpadding="0px" cellspacing="0px" border="1">';
		$table.='<tr>';
		foreach($this->columns as $column){
			$table.='<td style="width:'.$column['width'].'cm;color:'.$column['color'].';background-color:'.$column['background'].';text-align:center;font-family:'.$this->headFont.';font-size:'.$this->headFontSize.';font-weight:'.$this->headFontWeight.';font-style:'.$this->headFontStyle.';background-color:'.$this->headGgColor.'">'.$column['name'].'</td>';
		}
		$table.='</tr>';
		foreach($data as $row){
			$table.='<tr>';
			foreach($this->columns as $column){
				$table.='<td style="width:'.$column['width'].'cm;color:'.$column['color'].';background-color:'.$column['background'].';text-align:'.$column['align'].';word-break:break-all;font-family:'.$this->font.';font-size:'.$this->fontSize.';font-weight:'.$this->fontWeight.';font-style:'.$this->fontStyle.';">'.$row[$column['content']].'</td>';
			}
			$table.='</tr>';
		}
		$table.='</table>';
		return $table;
	}

	function reporterInterface($report){
		$html = '
		<table class="reporterInterface" border="1" id="reporterInterfaceTable">
			<tr>
				<td class="toolsTd">
					<table>
						<tr>
							<td>Page</td>
							<td>
								<select name="page" onchange=openPage(this.value)>';
									for($p = 0; $p < count($this->pages); $p++){
										$html.='<option value="'.$p.'">Page Number '.($p+1).'</option>';
									}
									$html.='<option value="all">All Pages</option>
								</select>
							</td>
							<td><a href="javascript:;" title="First Page" onclick=firstPage();><img src="../img/reporter/print_first.png" width="16px" height="16px"></a></td>
							<td><a href="javascript:;" title="Previous Page" onclick=previousPage();><img src="../img/reporter/print_previous.png" width="16px" height="16px"></a></td>
							<td><input type="text" name="pageNumber" value="" style="width:30px;text-align:center;"></td>
							<td><a href="javascript:;"  title="Next Page" onclick=nextPage();><img src="../img/reporter/print_next.png" width="16px" height="16px"></a></td>
							<td><a href="javascript:;"  title="Last Page" onclick=LastPage();><img src="../img/reporter/print_last.png" width="16px" height="16px"></a></td>
							<td><a href="javascript:;" title="Print" onclick=window.print();><img src="../img/reporter/printer.png" width="16px" height="16px"></a></td>							
							<td><a href="javascript:;" title="Zoom In" onclick=zoomIn();><img src="../img/reporter/z_in.png" width="16px" height="16px"></a></td>
							<td><a href="javascript:;" title="Zoom Out" onclick=ZoomOut();><img src="../img/reporter/z_out.png" width="16px" height="16px"></a></td>
							<td><a href="javascript:;" title="Normal View" onclick=NoZoom();><img src="../img/reporter/z_nor.png" width="16px" height="16px"></a></td>
							<td><a href="javascript:;" title="Edit View" onclick=toggleAddDialog("1","editViewDiv");><img src="../img/reporter/edit_prv.png" width="16px" height="16px"></a></td>
						</tr>
					</table>
				</td>
			<tr>
			<tr>
				<td id="reportPagesHolder">
					<div class="reportHolder" align="center" id="reportPages">
						<div id="rep">
						'.$report.'
						</div>
					</div>
				</td>
			<tr>
		</table>
		<div class="dialog-container" id="editViewDiv">            
		<div class="dialog" style="width:80%;height:80%">
				<form name="customerForm" method="post">						
						<div align="center"><h4>Edit Report Layout</h4></div>
						<div class="dialog-title">Paper Size</div>
						<div class="dialog-body">
								Width <input type="text" name="PaperWidth" id="PaperWidth" value="'.$this->width.'"  style="width:15%" required> <span style="color:red">CM</span>
								Height <input type="text" name="PaperHeight" id="PaperHeight" value="'.$this->height.'"  style="width:15%" required> <span style="color:red">CM</span>
								Rows <input type="text" name="rows" id="rows" value="'.$this->pageRows.'"  style="width:15%" required>
						</div>
						<div class="dialog-title">Header & Footer</div>
						<div class="dialog-body">
								Header <input type="text" name="HeaderHeight" id="HeaderWidth" value="'.$this->headHeigh.'"  style="width:25%" required> <span style="color:red">CM</span>
								Footer <input type="text" name="FooterHeight" id="FooterHeight" value="'.$this->footHeigh.'" style="width:25%" required> <span style="color:red">CM</span>
						</div>
						<div class="dialog-title">Font Size & Style</div>
						<div class="dialog-body">
								Font <input type="text" name="Font" id="Font" value="'.$this->font.'"  style="width:15%" required>	
								Size <input type="text" name="FontSize" id="FontSize" value="'.$this->fontSize.'"  style="width:15%" required>
								Weight <select  name="fontWeight" id="fontWeight" style="width:15%"><option value=""';
								if($this->fontWeight == ""){
									$html.=' selected ';
								}
								$html.='>Normal</option><option value="bold"';
								if($this->fontWeight == "bold"){
									$html.=' selected ';
								}
								$html.='>Bold</option></select>
								Style <select  name="fontStyle" id="fontStyle" style="width:15%"><option value="italic"';
								if($this->fontStyle == "italic"){
									$html.=' selected ';
								}
								$html.='>Italic</option>
								<option value=""';
								if($this->fontStyle == ""){
									$html.=' selected ';
								}
								$html.='>None</option></select>
						</div>
						<div class="dialog-title">Table Header Font Size & Style</div>
						<div class="dialog-body">
								Font <input type="text" name="HeadFont" id="Font" value="'.$this->font.'"  style="width:15%" required>			
								Size <input type="text" name="HeadFontSize" id="HeadFontSize" value="'.$this->fontSize.'"  style="width:15%" required>
								Weight <select  name="HeadfontWeight" id="HeadfontWeight" style="width:15%"><option value=""';
								if($this->headFontWeight == ""){
									$html.=' selected ';
								}
								$html.='>Normal</option><option value="bold"';
								if($this->headFontWeight == "bold"){
									$html.=' selected ';
								}
								$html.='>Bold</option></select>
								Style <select  name="headfontStyle" id="headfontStyle" style="width:15%"><option value="italic"';
								if($this->headFontStyle == "italic"){
									$html.=' selected ';
								}
								$html.='>Italic</option>
								<option value=""';
								if($this->headFontStyle == ""){
									$html.=' selected ';
								}
								$html.='>None</option></select>
								Bg <input type="color" name="HeadBg" id="HeadBg" value="'.$this->headGgColor.'"  style="width:15%" required>
						</div>
						<div class="dialog-title">Report Columns Sstup</div>
						<div style="width:100%;height:100%;overflow-y:scroll;overflow-x:hidden;">';
						$columnCount = 1;
						foreach($this->columns as $column){
							$html.='
							<div class="dialog-title">Column '.$columnCount.'</div>
							<div class="dialog-body">
								<table width="100%">
									<tr>
										<td width="15%" align="center">Name</td>
										<td width="35%"><input type="text" style="width:100%" name="name[]" value="'.$column['name'].'"></td>
										<td width="15%" align="center">Width</td>
										<td width="35%"><input type="text" style="width:100%" name="width[]" value="'.$column['width'].'"></td>										
									</tr>
									<tr>
										<td width="15%" align="center">Color</td>
										<td width="35%"><input type="color" style="width:100%" name="color[]"  value="'.$column['color'].'"></td>
										<td width="15%" align="center">Align</td>
										<td width="35%"><select style="width:100%" name="align[]"><option value="left"';
										if($column['align'] == "left"){
											$html.=' selected ';
										}
										$html.='>Left</option><option value="right"';
										if($column['align'] == "right"){
											$html.=' selected ';
										}
										$html.='>Right</option><option value="center"';
										if($column['align'] == "center"){
											$html.=' selected ';
										}										
										$html.='>Center</option></select></td>
									</tr>
									<tr>
										<td width="15%" align="center">BgColor</td>
										<td width="35%"><input type="color" style="width:100%" name="bgcolor[]" value="'.$column['background'].'"></td>
										<td></td>
										<td></td>
									</tr>
								</table>
								
							</div>';
							$columnCount++;
						}
						$html.='
						</div>
						<div class="dialog-buttons" align="center">
							<button type="submit" class="button">View</button>
							<button type="button" class="button" onclick=toggleAddDialog("0","editViewDiv");>Cancel</button>
						</div>

				</form>
		</div>
</div>		
		<script>openPage(0);</script>';
		return $html;
	}


}
?>