var ForceUsers_Ping = function()
{
    this.init = function ( params_marams )
    {
        //Adding command into the ping queue
        OW.getPing().addCommand('force_users_check_status',
            {
                params: {},
                before: function ()
                {
                },
                after: function (data)
                {
                    var totalAmount = data.totalAmount;
                    var rest = data.rest;
                    var status = data.status;
                    if( rest == 0 )
                    {
                        $('#current_div').html('');
                        $('#total_div').html('');
                    }
                    else
                    {
                        $('#current_div').html(rest);
                        $('#total_div').html(totalAmount);
                    }
                    $('#total_div').html(totalAmount);
                    $('#status_div').html(status);
                    $('#totalOnlineUsersCount').empty();
                    $('#totalOnlineUsersCount').append(' ' + data.count);
                    if(status == 'complete')
                    {
                        $('#amountOnlineUsers_div').find('*').prop('disabled',false);
                        $('#action_buttons_div').find('*').prop('disabled',false);
                        $('#status_div').html('ready');
                    }
                }
            }).start(5000); // Time interval in milliseconds
    }
}