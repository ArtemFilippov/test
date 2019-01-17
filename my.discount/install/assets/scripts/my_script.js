function del(){
	$.post("/local/modules/my.discount/ajax.php",{},function(data){
		let t = JSON.parse(data);
		//console.log(t);
			if(t.progress == "continue"){
				del();
			}
			else{
			$(".progress").append('<p><strong>Шаг 3 из 3: Идет обработка файла и запись. Дождитесь сообщения об окончании выгрузки!</strong></p>');
			console.log("done");
			insert(t.files);
				
			}
	});
}

function insert(){
	$.post("/local/modules/my.discount/ajax.php",{},function(data){
		
		let t = JSON.parse(data);
		
			if(t.progress == "continue"){
				insert();
			}
			else{
				$('input[name=xml_file]').val("");
				$(".progress").append('<p><strong>Выгрузка завершена!</strong></p>');
			console.log("done");
			$("form#xml_load").find("input[type='submit']").prop("disabled", false)
				return false;
			}
	});
}

$(document).ready(function(e){

	var data = new FormData();
	var files;

	$('input[name=xml_file]').change(function(){
		$(".error p").text("");
	    files = this.files;
	    
	    if(files[0].type !== "text/xml"){
	    	$(".error p").text("<strong>Не верный формат файла!</strong>");
	 	}
	 	else{

	 	}
	});

	$("#xml_load").on("submit", function(e){
		$(".error p").text("");
		e.stopPropagation();
		e.preventDefault();

		

	    $.each( files, function( key, value ){
	    	
	        data.append( "file", value );
	    });
	 
	    // Отправляем запрос
	 
	 	
	 	if(files[0].type == "text/xml"){

	 		/*var xhr = new XMLHttpRequest();
			  
			  xhr.open("POST", "/local/modules/my.discount/ajax.php");
			  xhr.send(data);
			  
			  xhr.onreadystatechange = function() {
			  
			  	if( xhr.readyState == 4 ) {
			  		console.log(xhr);
			  	}
			  }*/
	 		
	 		$.ajax({
			        url: '/local/modules/my.discount/ajax.php',
			        type: 'POST',
			        data: data,
			        cache: false,
			        dataType: 'json',
			        processData: false, // Не обрабатываем файлы (Don't process the files)
			        contentType: false, // Так jQuery скажет серверу что это строковой запрос
			        beforeSend: function(){
			        	$("form#xml_load").find("input[type='submit']").prop("disabled", true);
			        	$(".progress").append('<p><strong>Шаг 1 из 3: Загрузка файла</strong></p>');
			        },
			        success: function( respond, textStatus, jqXHR ){
			 
			            // Если все ОК
			 		//console.log(respond, textStatus, jqXHR);
			            if( respond.success == 'true' && respond.step ==2){
			             	 
			                $(".progress").append('<p><strong>Файл загружен</strong></p>');
				                
				                if(respond.step ==2){
				                		/*$.ajax({
									        url: '/local/modules/my.discount/ajax.php',
									        type: 'POST',
									        data: {},
									        cache: false,
									        dataType: 'json',
									        processData: false, // Не обрабатываем файлы (Don't process the files)
									        contentType: false, // Так jQuery скажет серверу что это строковой запрос
									        beforeSend: function(){
									        	$(".progress").append('<p>Шаг 2 из 3: Очистка данных</p>');
									        },
									        success: function( respond1, textStatus1, jqXHR1 ){
									        	
									        	if(respond1.progress == "continue" && respond1.step == 2){*/
									        		//del();
									      /*  	}
									        	
									        	else{
									        		//$(".error p").text('ОШИБКИ ОТВЕТА сервера: ' + jqXHR1 + textStatus1 + errorThrown1);
									        	}
									        	
									        },
									        error: function( jqXHR1, textStatus1, errorThrown1 ){
										        	$(".progress").html("");
										            $(".error p").text('ОШИБКИ ОТВЕТА сервера: ' + jqXHR1 + textStatus1 + errorThrown1);
										        }
										    });*/

										   			$(".progress").append('<p><strong>Шаг 2 из 3: Очистка предыдущих данных</strong></p>');
									        		del();
								        	
								        	
				                }
			            }
			            else{
			            	
			                $(".progress").html("");
			                $(".error p").text('ОШИБКИ ОТВЕТА сервера: ' + respond);
			            }
			            //console.log(respond.success == "true");
			        },
			        error: function( jqXHR, textStatus, errorThrown ){
			        	console.log(jqXHR, textStatus, errorThrown);
			        	$(".progress").html("");
			            $(".error p").text('ОШИБКИ ОТВЕТА сервера: ' + jqXHR + textStatus + errorThrown);
			        }
			    });

		 	}
		 	else{
		 		$(".error p").text("Не верный формат файла!");
		 	}
			    

			});
});

