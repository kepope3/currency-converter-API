//function to validate forms input
function validate() 
{
    var input,shortenedString;
    if (($('#code').val().length) > 2)
    {
        
    }
}

function restrictEntry()
{
    var option = $('#sel1').val();
    switch(option)
    {
        case 'PUT': 
            $('#name').attr('disabled',false);
            $('#code').attr('disabled',false);
            $('#rate').attr('disabled',false);
            $('#countries').attr('disabled',false);            
            break;
        case 'POST':
            $('#name').attr('disabled',true);
            $('#code').attr('disabled',false);
            $('#rate').attr('disabled',false);
            $('#countries').attr('disabled',true);
            break;
        case 'DELETE':
            $('#name').attr('disabled',true);
            $('#code').attr('disabled',false);
            $('#rate').attr('disabled',true);
            $('#countries').attr('disabled',true);
            break;
        case 'STATS':
            $('#name').attr('disabled',true);
            $('#code').attr('disabled',false);
            $('#rate').attr('disabled',true);
            $('#countries').attr('disabled',true);
            break;
            
    }
}