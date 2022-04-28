<div id="menu">
    <?php
        $getMainSectionsQuery = 'select * from sections where section_id > 0 and section_id not in(select main_section_id from sections)';
        $getMainSectionsResult = mysql_query($getMainSectionsQuery)or die("error getMainSectionsQuery not done ".mysql_error());
        echo '        
        <div class="container">            
            <input type="text" style="width:100%;" name="sectionName">
            <div class="row">';
                while($section = mysql_fetch_array($getMainSectionsResult)){
                    $data = '';
                    if($section['data'] != ""){
                        $dataAttr = explode("&",$section['data']);
                        foreach($dataAttr as $attr){
                            $attrArray = explode(":",$attr);
                            $data.=' data-'.$attrArray[0].'="'.$attrArray[1].'" ';
                        }
                    }else{
                        $data = '';
                    }
                    echo '
                    <div class="col d-flex justify-content-center text-center">                        
                        <div class="container menuItem" data-section-name="'.$section['section_url'].'" '.$data.'>
                            <div class="row">
                                <div class="col">
                                    <img src="'.$section['icon'].'" width="50px">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    '.$section['section_name'].'
                                </div>
                            </div>
                        </div>
                    </div>';
                }
                echo '
            </div>
        </div>';
    ?>
    
</div>