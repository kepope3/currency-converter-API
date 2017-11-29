function ajaxOUT(dataType)
{
    var outputData;
    var resetTyp = "";
    var urlAdd = "index.php";  
    if (dataType == "PUT")
    {
        outputData = "code="+$('#code').val()+"&name="+$("#name").val()+
                "&rate="+$("#rate").val()+"&countries="+$("#countries").val();
    }
    //if user makes stats request, a GET request is sent
    else if (dataType == "STATS")
    {
        outputData = "stats=''&code="+$('#code').val();
        dataType = 'GET';
    }
    //update currency rate
    else if (dataType == "POST")
    {
        outputData = "code="+$('#code').val()+"&rate="+$("#rate").val();
    }
    else if (dataType == "DELETE")
    {
        outputData = "code="+$('#code').val();
    }
    else if (dataType == "resetCurrAry")
    {
        resetTyp = dataType;
        dataType = 'GET';
        outputData = "ARY=''";        
    }
    else if (dataType == "resetCurrXML")
    {
        resetTyp = dataType;
        dataType = 'GET';
        outputData = "XML=''";
    }
    $.ajax({
        url: urlAdd,
        type: dataType,
        data: outputData,
		//INCLUDE CONTENT TYPE http://stackoverflow.com/questions/18701282/what-is-content-type-and-datatype-in-an-ajax-request
        async: false,
        success : function(data) {
			//data should be http response?
            $("#panBody").html(data);
            
        }
    });
    if (resetTyp == "resetCurrAry")
        alert("Reset Ary Complete!");
    else if (resetTyp == "resetCurrXML")
        alert("Reset Curr XML Complete!");
    
}