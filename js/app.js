var menuBtn = document.getElementById("mainMenu");
start = 0;
limit = 10;
function reloadSection(){    
    $("#load_data").data('start',0);
    $("#load_data").data('limit',10);
    var targetData = {limit:$("#load_data").data('limit'), start:$("#load_data").data('start'),section:$("#load_data").data('sectionName'),action:'load'};
    $.each($("#load_data").data(),function(key,value){
        if(key != 'section-name' && key != 'start' && key != 'limit'){
            targetData[key] = value;
        }
    });

    start = 0;
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            $('#load_data').html(data);
            $("#load_data").data('start',Number(Number($("#load_data").data('start'))+ Number($("#load_data").data('limit'))));
            if(data == ''){
                $('#load_data_message').html("<button type='button' class='btn btn-info'>No Data Found</button>");
                action = 'active';
            }else{
                $('#load_data_message').html('<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
                action = 'inactive';
            }
        }
    });
}

$(document).ready( 
    function (){
        $('#mainMenuBtn').on('click',function(){    
            $('#menu').toggle();
        });       

        $('#Loader').scroll(
            function(){
                if($('#Loader').scrollTop() + $('#Loader').height() > $("#load_data").height() && action != 'active'){
                    action = 'active';                    
                    setTimeout(function(){
                        var targetData = {limit:$("#load_data").data('limit'), start:$("#load_data").data('start'),section:$("#load_data").data('sectionName'),action:'load'};
                        $.each($("#load_data").data(),function(key,value){
                            if(key != 'section-name' && key != 'start' && key != 'limit'){
                                targetData[key] = value;
                            }
                        });                    
                        $.ajax({
                            url:"ajax.php",
                            method:"get",
                            data:targetData,
                            cache:false,
                            success:function(data){
                                $("#load_data").data('start',Number(Number($("#load_data").data('start'))+ Number($("#load_data").data('limit'))));
                                $('#load_data').append(data);
                                if(data == ''){
                                    $('#load_data_message').html("<button type='button' class='btn btn-info'>No Data Found</button>");
                                    action = 'active';
                                }else{
                                    $('#load_data_message').html('<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
                                    action = 'inactive';
                                }
                            }
                        });
                    }, 1000);
                }
            }
        );


        $('.menuItem').on('click',function(){
            $('#menu').toggle(false);
            $("#load_data").data('sectionName',$(this).data('section-name'));
            $('.addBtn').data('sectionName',$(this).data('section-name'));
            $("#load_data").data('start',0);
            $("#load_data").data('limit',10);
            var targetData = {limit:$("#load_data").data('limit'), start:$("#load_data").data('start'),section:$("#load_data").data('sectionName'),action:'load'};
            $.each($(this).data(),function(key,value){
                if(key != 'section-name'){
                    $("#load_data").data(key,value);
                    $('.addBtn').data(key,value);
                    targetData[key] = value;
                }
            });
            start = 0;
            $.ajax({
                url:"ajax.php",
                method:"get",
                data:targetData,
                cache:false,
                success:function(data){            
                    $('#load_data').html(data);
                    $("#load_data").data('start',Number(Number($("#load_data").data('start'))+ Number($("#load_data").data('limit'))));
                    if(data == ''){
                        $('#load_data_message').html("<button type='button' class='btn btn-info'>No Data Found</button>");
                        action = 'active';
                    }else{
                        $('#load_data_message').html('<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
                        action = 'inactive';
                    }
                }
            });
        });

        $('.addBtn').on('click',function(){
            var targetData = {section:$(this).data('sectionName'),action:'add'};
            $.each($(this).data(),function(key,value){
                if(key != 'section-name'){
                    targetData[key] = value;
                }
            });
            $.ajax({
                url:"ajax.php",
                method:"get",
                data:targetData,
                cache:false,
                success:function(data){            
                    $('#overlayContent').html(data);
                    $('#overlay').toggle(true);
                }
            });
        });




        $(window).click(function() {
            $('#overlay').toggle(false);
        });
          
        $('#overlayContent').click(function(event){
           event.stopPropagation();
        });
          
        $('#overlayContent').on('click','button:submit',function(event){
            event.preventDefault();
            var targetAction = $(this).data('action');
            var formData = $(this).parents('form:first').serialize();                        
            $.ajax({
                url:$(this).parents('form:first').attr('action'),
                method:$(this).parents('form:first').attr('method'),
                data:formData,
                cache:false,
                success:function(data){         
                    if(String(data).length > 0){
                        //alert(data);                        
                        var scripts, scriptsFinder=/<script[^>]*>([\s\S]+)<\/script>/gi;
                        while(scripts=scriptsFinder.exec(data)){
                            eval.call(window,scripts[1]);                         
                        }
                    }else{              
                        if(targetAction == 'close'){
                            $('#overlay').toggle(false);
                        }else{
                            reloadSection();
                            $('#overlay').toggle(false);
                        }
                    }
                }
            });
        });

        $('#overlayContent').on('click','.submit',function(event){
            var formData = $(this).parents('form:first').submit();
        });

        $('#overlayContent').on('click','.submitBtn',function(event){
            event.preventDefault();
            var targetAction = $(this).data('action');
            var formData = $(this).parents('form:first').serialize();                        
            $.ajax({
                url:$(this).parents('form:first').attr('action'),
                method:$(this).parents('form:first').attr('method'),
                data:formData,
                cache:false,
                success:function(data){         
                    if(String(data).length > 0){
                        var scripts, scriptsFinder=/<script[^>]*>([\s\S]+)<\/script>/gi;
                        while(scripts=scriptsFinder.exec(data)){
                            eval.call(window,scripts[1]);                         
                        }
                    }else{              
                        if(targetAction == 'close'){
                            $('#overlay').toggle(false);
                        }else{
                            reloadSection();
                            $('#overlay').toggle(false);
                        }
                    }
                }
            });
        });


        $('#overlayContent').on('change','select',function(event){
            event.preventDefault();
            var targetId = '';
            var targetData = {id:$(this).val()};
            $.each($(this).data(),function(key,value){
                if(key == 'target'){
                    targetId = value;
                }
                targetData[key] = value;
            });
            if(!targetId){
                return;
            }            
            $.ajax({
                url:'ajax.php',
                method:'get',
                data:targetData,
                cache:false,
                success:function(data){         
                    $('#'+targetId).attr('value',data);
                }
            });
        });


        $('#load_data').on('click','.card-link',function(){
            var sectionName = $(this).data('section');
            var actionName = $(this).data('action');
            var actionTarget = $(this).data('target');
            var objectId = $(this).data('id');
            var targetData = {section:sectionName,action:actionName,id:objectId};
            $.each($(this).data(),function(key,value){
                if(key != 'section' && key != 'action' && key != 'id'){
                    targetData[key] = value;
                }
            });
            $.ajax({
                url:"ajax.php",
                method:"get",
                data:targetData,
                cache:false,
                success:function(data){            
                    if(actionName == 'edit' || actionTarget == "form"){
                        $('#overlayContent').html(data);
                        $('#overlay').toggle(true);
                    }else{
                        var scripts, scriptsFinder=/<script[^>]*>([\s\S]+)<\/script>/gi;
                        while(scripts=scriptsFinder.exec(data)){
                            eval.call(window,scripts[1]);                         
                        }
                        reloadSection();
                    }
                }
            });

        });


        $('#overlayContent').on('click','.unitsReload',function(event){
            event.preventDefault();
            var targetData = {id:$(this).data('id'),section:"products",action:"units"};
            $.ajax({
                url:"ajax.php",
                method:"get",
                data:targetData,
                cache:false,
                success:function(data){            
                    $('#overlayContent').html(data);                    
                    $('#overlay').toggle(true);
                }
            });
        });

        $('#overlayContent').on('click','.unitLink',function(event){
            event.preventDefault();
            var targetData = {id:$(this).data('id'),section:"products",action:"units",oldId:$(this).data('old')};
            $.ajax({
                url:"ajax.php",
                method:"get",
                data:targetData,
                cache:false,
                success:function(data){            
                    $('#overlayContent').html(data);
                    $('#overlay').toggle(true);
                }
            });
        });

        $('#overlayContent').on('click','.deleteUnit',function(event){
            event.preventDefault();
            var targetData = {id:$(this).data('old'),section:"products",action:"deleteUnit",productId:$(this).data('id')};
            $.ajax({
                url:"ajax.php",
                method:"get",
                data:targetData,
                cache:false,
                success:function(data){            
                    $('#overlayContent').html(data);
                    $('#overlay').toggle(true);
                }
            });
        });



        

    }
);

function select(object){
    object.select();
}

function calcTotalOrder(){
    var total = document.getElementById("total");
    var discount = document.getElementById("discount");
    var extra = document.getElementById("extra");
    var overall = document.getElementById("overall");
    var prices = document.getElementsByName("price[]");
    var quantities = document.getElementsByName("quantity[]");
    var totals = document.getElementsByName("itemTotal[]");
    var totalValue = 0;
    for(x = 0; x < prices.length; x++){
        totals[x].value = Number(Number(prices[x].value) * Number(quantities[x].value)).toFixed(2);
        totalValue = Number(totalValue) + Number(totals[x].value);
    }
    total.value = Number(totalValue).toFixed(2);
    overall.value = Number(Number(Number(totalValue) + Number(extra.value)) - Number(discount.value)).toFixed(2)
    calcCredits();
}

function sendCode(event,object){
    if(event.keyCode == 13){
        var targetData = {type:$('#barCode').data('type'),section:"orders",action:"getProduct",stockId:$('#barCode').data('stock'),code:$('#barCode').val()};
        document.getElementById("barCode").value="";
        $.ajax({
            url:"ajax.php",
            method:"get",
            data:targetData,
            cache:false,
            success:function(data){            
                $('#orderProducts').append(data);
                calcTotalOrder();
            }
        });
    }
}

function sendCode2(object){
    var targetData = {type:$('#barCode').data('type'),section:"orders",action:"getProduct",stockId:$('#barCode').data('stock'),code:$('#barCode').val()};
    document.getElementById("barCode").value="";
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            $('#orderProducts').append(data);
            calcTotalOrder();
        }
    });
}

function removeProduct(object){
    document.getElementById("orderProducts").removeChild(object.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement);
    calcTotalOrder();
}

function getUnitPrice(object,product,orderType){
    var container = document.getElementById("orderProducts");
    var index;
    var counter = 0;
    var targetData = {type:orderType,section:"orders",action:"getUnitPrice",productId:product,unit:object.value};
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            object.parentElement.parentElement.parentElement.rows[2].cells[0].children[0].value=data;
            calcTotalOrder();
        }
    });            
}


function catcTotalPermit(){
    var type = document.getElementsByName("type")[0].value;
    var sum = document.getElementsByName("total")[0].value;
    var companyCredit = document.getElementById("ccredit").value;
    var treasuryCredit = document.getElementById("tcredit").value;
    var companyCreditAfter = document.getElementById("companyCredit");
    var treasuryCreditAfter = document.getElementById("treasuryCredit");
    var companyType =  document.getElementById("ctype").value;
    if(type == 5){
        treasuryCreditAfter.value = Number(Number(treasuryCredit) + Number(sum)).toFixed(2);
    }else if(type == 6){
        treasuryCreditAfter.value = Number(Number(treasuryCredit) - Number(sum)).toFixed(2);
    }
    
    if((type == 5 && companyType == 1) || (type == 6 && companyType == 2)){
        companyCreditAfter.value = Number(Number(companyCredit) - Number(sum)).toFixed(2);
    }else{
        companyCreditAfter.value = Number(Number(companyCredit) + Number(sum)).toFixed(2);
    }
}



function changeCompany(object){
    var targetData = {section:"permits",action:"changeCompany",companyId:object.value};
    document.getElementById("ctype").value=object.options[object.selectedIndex].dataset.type;
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            document.getElementById("ccredit").value=data;            
            catcTotalPermit();
        }
    });            
}


function changeTreasury(object){
    var targetData = {section:"permits",action:"changeTreasury",treasuryId:object.value};
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            document.getElementById("tcredit").value=data;            
            catcTotalPermit();
        }
    });
}



function calcCredits(){
    var type = document.getElementsByName("type")[0].value;    
    var companyCredit = document.getElementById("ccredit").value;
    var treasuryCredit = document.getElementById("tcredit").value;
    var treasuryCreditAfter = document.getElementById("treasuryCredit");
    var companyCreditAfter = document.getElementById("companyCredit");
    var overAll = document.getElementsByName("overall")[0].value;    
    var paid = document.getElementsByName("paid")[0].value;    
    if(type == 1 || type == 4){
        treasuryCreditAfter.value = Number(treasuryCredit) - Number(paid);
    }else if(type == 2 || type == 3){
        treasuryCreditAfter.value = Number(treasuryCredit) + Number(paid);
    }

    if(type == 1 || type == 3){
        companyCreditAfter.value = Number(companyCredit) + Number(Number(overAll) - Number(paid));
    }else if(type == 2 || type == 4){
        companyCreditAfter.value = Number(companyCredit) - Number(Number(overAll) - Number(paid));
    }
}

function setPaid(object){
    if(object.checked == true){
        document.getElementsByName("paid")[0].value = document.getElementsByName("overall")[0].value;
    }else{
        document.getElementsByName("paid")[0].value = 0;
    }
    calcCredits();
}

function changeFinanceCategory(object){
    var targetData = {section:"permits",action:"changeCategory",categoryId:object.value};
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            document.getElementById("companyContainer").innerHTML = data;
            catcTotalPermit();
        }
    });
}

function getProducts(object){
    var targetData = {section:"reports",action:"getProducts",categoryId:object.value};
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            document.getElementById("productsList").innerHTML = data;
        }
    });
}

function getProductUnits(object){
    var targetData = {section:"reports",action:"getProductUnits",productId:object.value};
    $.ajax({
        url:"ajax.php",
        method:"get",
        data:targetData,
        cache:false,
        success:function(data){            
            document.getElementById("unitsList").innerHTML = data;
        }
    });
}
