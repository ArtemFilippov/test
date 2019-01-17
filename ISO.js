

function Query(method, path, type){

	var self = this;
	self.method = method || "POST";
	self.path = path || "/fund/ISO.js";
	self.type = type;
	self.object = {};
	self.result = {};
	var XHR = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
	self.xhr = new XHR();
	
	self.preperesend = function(){
		//self.xhr.addEventListener('upload', self.complete, false);
		self.xhr.open(self.method, self.path, self.type);
		self.xhr.setRequestHeader('Accept', 'text/javascript, application/javascript, application/ecmascript, application/x-ecmascript, */*; q=0.01');
		self.xhr.setRequestHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
		self.xhr.setRequestHeader('Content-type', 'text/javascript; charset=utf-8');
		//self.xhr.withCredentials = true;

		self.ready();

		/*if(Object.keys(self.getObject()).length>0){
			 self.xhr.send(self.getObject());
		}
		else {

			self.xhr.send(null);
		}*/


	}

	self.setObject = function(body){
		if(typeof(body)=="object" && Object.keys(body).length>0)
			self.object = body;
		else 
			self.object = {};

		return self.object;
	}
	self.getObject = function(){
		return self.object;
	}
	self.getResult = function(){
		return self.result;
	}
	self.ready = function(){
		'use strict';
		//var start = new Date().getTime();
		
		self.xhr.onreadystatechange = function(){
		//console.log(self.xhr.readyState + " " + self.xhr.status + " " + self.xhr.responseText);
		//self.result = self.xhr.responseText;
			if(self.xhr.readyState ==4){
				   	if(self.xhr.status == 200){
				   		
				   		self.result = self.prepereObject(self.xhr.responseText);
				   	
		  				//console.log(self.result);
				   	} 
		   		}

		}

		//return self.xhr.responseText;
	}
	self.getResponse = function(body){
		self.setObject(body);
		//alert(self.getObject());
		try{
			//localStorage.removeItem("test");
			if(typeof(self.getObject()) != "object"){
				//alert(Object.keys(self.getObject()).length);
				return;
			}
			else if(typeof(self.getObject()) == "object"){
				
				self.preperesend();
				
				if(Object.keys(self.getObject()).length>0){
					 self.xhr.send(self.getObject());
				}
				else {

					self.xhr.send(null);
				}

				return self.result;
			}
			else {
				//alert(Object.keys(self.getObject()).length);
				return self.getObject();
			}

			return self.result;

		}catch(e){
			//alert();
		}
		
	}
	self.setResult = function(object){
		self.result = object;
	}

	self.prepereObject = function(object){
		if(typeof(object)=="string"){
			return JSON.parse(object);
		}else{
			return object;
		}
	}

	self.complite = function(){
		//console.log(self.xhr.responseText);
	}

}

var S = new Query("get", "/fund/ISO.js" + "?Date=" + new Date().getTime(), true);

S.getResponse();

function GSearch(){

	self = this;

	//self.input = document.getElementById(input);
	self.resObj ={};

	var past_value = "";
	

	self.clearInput =  function(input){
		var n = document.getElementById(input);
		n.value = "";
	}

	self.test = function(str, search){
		var pattern = new RegExp(search, 'gi');
		return str.match(pattern);
	}

	self.search = function(search, filter){
		if(typeof(S) == 'object' && typeof(search) == "string"){
			self.resObj={};
			var i =0;
			//console.log(typeof(search) + " " + typeof(filter));
			if(search == "filter" && filter !=""){
				for (var key in S.result) {
					if(!!key && S.result[key]['PROPERTY_1405_ENUM_ID'] == filter){
						self.resObj[key] = S.result[key];
					    i++;
					}
					 
				}
			}
			else{
				for (var key in S.result) {
				
				    if(self.test(S.result[key]["PROPERTY_1402_VALUE"], search)){
				  		self.resObj[key] = S.result[key];
				  		i++;
				    }
				}
			}
			self.resObj["count"] = i;
			//console.log(self.resObj);
			return self.resObj;
		}
	}

	self.getInstance = function(value){
		
		self.checkValue(value);
			
	}

	self.checkValue = function(value){
		
		var empty = document.getElementById("mh29_empty");

		if(typeof(value)!="string"){
			return;
		} else if(typeof(value)=="string"){
			
		if(past_value == value){return;}
		else{
			
			self.initiate(value);
			if(self.resObj['count']>0){
				self.create("mh29_uklist_table", self.resObj);
				//self.empty.style.display = "none";
			}else if(self.resObj['count']==0){
				self.create("mh29_uklist_table", self.resObj);
				
	  			empty.style.display = "block";
			}
			else {
				self.create("mh29_uklist_table", S.result);
				//self.empty.style.display = "none";
			}
		}
			
			past_value = value;

		}else{
			return;
		}

	}

	self.initiate = function(value, filter){
		return self.search(value, filter);
	}

	self.create = function(elem, object){
		
		if(typeof(object)!="object"){
			return;
		}
		else{
			var table = document.getElementById(elem).querySelector('table tbody');
			table.innerHTML = "";
			var empty = document.getElementById("mh29_empty");
			var tr = document.createElement('tr');
			var td = document.createElement('td');
			
		  	empty.style.display = "none";
			//if(object["count"]>0){
			for (var key in object) {
				//console.log(object[key]);
				if(key!="count"){
					td.innerHTML += "<div class = 'kb_item'><a href='"+ object[key]["DETAIL_PAGE_URL"] +"'>" + object[key]["PROPERTY_1402_VALUE"] + "</div>";
				}
			}
			tr.appendChild(td);
			table.appendChild(tr);
			
		}
	}

    self.getFilter = function(filter){
    	self.search("filter", filter);
        self.create("mh29_uklist_table", self.resObj);
    }

}
var R = new GSearch();
