
var MEMBERX_ResultList = (function($)
{
    var excludeList = [];
    var contentNode = undefined;
    var preloader = $('<div class="ow_left ow_memberx_preloader_box"><div class="ow_fw_menu ow_preloader"></div></div>');
    
    var respunderUrl = undefined;
    
    var orderType = undefined;
    var listId = undefined;
    
    var startPage = 1; 
    var endPage = 1; 
    var count = 20; 
    var process = false
    var allowLoadNext = true;
    var allowLoadPrev = false;
    
    var prevLinkNode;
    
    var utils = {
        
        setPrevProsessStatus: function( value )
        {
            var self = this;

            process = value;

        },
        
        setNextProsessStatus: function( value )
        {
            var self = this;

            process = value;

        }
    };
    
    return {
        init: function(params, node) {
            var self = this;
            
            excludeList = params['excludeList'];
            contentNode = node;
            respunderUrl = params['respunderUrl'];
            orderType = params['orderType'];
            listId = params['listId'];
            
            if ( params['page'] )
            {
                startPage = +params['page'];
                endPage = startPage;
                
                if ( startPage > 1 )
                {
                    allowLoadPrev = true;
                }
            }
            
            if ( params['count'] )
            {
                count = params['count'];
            }
            
            prevLinkNode = $('.memberx_load_earlier_profiles');
            prevLinkNode.find('a').click( function() { self.loadPrevious(); } );
            
            var timerId;
            
            if (params['noScroll']){
                //do no
            }else{
                $(window).scroll(function( event ) {
                    self.tryLoadData();
                    clearTimeout(timerId);
                    timerId = setTimeout(function(){self.changeUrl();},40);
                });
            }
            
            /*$(document).on('mouseover', '.ow_photo_item_wrap', function(event){
                $(this).find('.ow_memberx_user_info').show();

            });
            
            $(document).on('mouseout', '.ow_photo_item_wrap', function(event){
                $(this).find('.ow_memberx_user_info').hide();
            });*/
            
            
            $(document).on('click', 'a.invite-to-event', function(event){
                var id = this.getAttribute('data-id');
                if (id){
                    EventInviteWindow = OW.ajaxFloatBox('MEMBERX_CMP_EventSelector', [id], {
                        width: '480'
                    } );
                }
            });
            
            $(document).on('click', 'a.invite-to-group', function(event){
                var id = this.getAttribute('data-id');
                if (id){
                    GroupInviteWindow = OW.ajaxFloatBox('MEMBERX_CMP_GroupSelector', [id], {
                        width: '480'
                    } );
                }
            });
            
            
            
            
            $(document).on('click', 'a.send-virtual-gift', function(event){
                var id = this.getAttribute('data-id');
                var floatboxTtile = this.getAttribute('data-title');
                if (id){
                   sendGiftFloatBox = OW.ajaxFloatBox(
                        "VIRTUALGIFTS_CMP_SendGift",
                        { recipientId: id },
                        { width : 580, title: floatboxTtile}
                    );
                }
            });
            
            
            $(document).on('click', 'a.open-chat-dialog', function(event){
                var id = this.getAttribute('data-id');
                var mailAction = this.getAttribute('mail-action');
                if (id){
                    if (mailAction == "true"){
                        OW.trigger('base.online_now_click', [id] );
                    }else{
                        self.sendChatMessage(id);
                    }
                    
                }
            });
            
            
            $(document).on('click', 'a.send-private-message', function(event){
                var data = this.getAttribute('mailbox-data');

                if (data){
                   OW.trigger("mailbox.open_new_message_form", JSON.parse(data));
                }
            });
            
            $(document).on('click', 'a.send-wink', function(event){
                var self = $(this);
                var id = this.getAttribute('data-id');
                var limited = this.getAttribute('data-limited');
                var button = $('.memberx_button', this);
                var data = {userId: params['userId'], partnerId: id, funcName: 'sendWink'};
                
                if (limited == 'true'){
                    OW.warning(params['wink_double_sent_error']);
                    return false;
                }
                
                if (id){
                    var orgClass = 'memberx_button_wink1';
                    if (button.hasClass('memberx_button_wink1')){
                        orgClass = 'memberx_button_wink1';
                        button.removeClass('memberx_button_wink1');
                    }
                    
                    if (button.hasClass('memberx_button_wink2')){
                        orgClass = 'memberx_button_wink2';
                        button.removeClass('memberx_button_wink2');
                    }

                    button.addClass('ow_inprogress');
                    button.addClass('owm_preloader');
                    
                    
                    $.ajax({
                        type: "POST",
                        url: params['winkRsp'],
                        data: data,
                        success: function (data) {

                            if (data.result){
                                button.addClass('memberx_button_wink2');
                                self.attr('data-limited', 'true');
                                OW.info(params['wink_success_msg']);
                            }else{
                                if (data.msg){
                                    OW.warning(data.msg);
                                }
                                button.addClass(orgClass);
                            }
                            
                        },
                        error: function (error) {
                            button.addClass(orgClass);
                            
                        },
                        dataType: 'json'
                    }).done(function () {
                        button.removeClass('ow_inprogress');
                        button.removeClass('owm_preloader');
                    });
                    
                }
            });
            
            
            $(document).on('click', 'a.add-bookmark', function(event){
                var self = $(this);
                var id = this.getAttribute('data-id');
                var button = $('.memberx_button', this);
                var data = {userId: id};
                
                if (id){
                    var orgClass = 'memberx_button_bookmark1';
                    if (button.hasClass('memberx_button_bookmark1')){
                        orgClass = 'memberx_button_bookmark1';
                        button.removeClass('memberx_button_bookmark1');
                    }
                    
                    if (button.hasClass('memberx_button_bookmark2')){
                        orgClass = 'memberx_button_bookmark2';
                        button.removeClass('memberx_button_bookmark2');
                    }

                    button.addClass('ow_inprogress');
                    button.addClass('owm_preloader');
       
                    $.ajax({
                        type: "POST",
                        url: params['bookmarkRsp'],
                        data: data,
                        success: function (data) {

                            if (data.mark === true){
                                button.addClass('memberx_button_bookmark2');
                                OW.info(params['bookmark_added_msg']);
                            }else{
                                button.addClass('memberx_button_bookmark1');
                                OW.info(params['bookmark_removed_msg']);
                            }
                            
                        },
                        error: function (error) {
                            button.addClass(orgClass);
                            
                        },
                        dataType: 'json'
                    }).done(function () {
                        button.removeClass('ow_inprogress');
                        button.removeClass('owm_preloader');
                    });
                    
                }
            });
            
            
            $(document).on('click', 'a.make-video-call', function(event){
                var id = this.getAttribute('data-id');
                if (id){
                    videoImRequest.getChatWindow(id);
                }
            });
            
            $(document).on('click', 'a.show-login-window', function(event){
                new OW_FloatBox({ $contents: $('#base_cmp_floatbox_ajax_signin')});
            });
            
            
            
            
            
            
            
        },
        
        sendChatMessage: function(opponentId){
            $('#ow_preloader_content_'+opponentId).removeClass('ow_hidden');
            $('#ow_chat_now_'+opponentId).addClass('ow_hidden');
            $.post(OWMailbox.openDialogResponderUrl, {
                    userId: opponentId,
                    checkStatus: 2
                }, function(data){

                    if ( typeof data != 'undefined'){
                        if ( typeof data['warning'] != 'undefined' && data['warning'] ){
                            OW.message(data['message'], data['type']);
                        }else if(typeof data['error'] != 'undefined' && data['error'] ){
                            OW.warning(data['error']);
                        }else{
                            if (data['use_chat'] && data['use_chat'] == 'promoted'){
                                OW.Mailbox.contactManagerView.showPromotion();
                            }else{
                                OW.Mailbox.usersCollection.add(data);
                                OW.trigger('mailbox.open_dialog', {convId: data['convId'], opponentId: data['opponentId'], mode: 'chat'});
                            }
                        }
                    }
                }, 'json').complete(function(){

                        $('#ow_chat_now_'+opponentId).removeClass('ow_hidden');
                        $('#ow_preloader_content_'+opponentId).addClass('ow_hidden');
                    });

        },
        
        addToExcludeList: function(items) {
            $.each( items, function( key, val ) {
                excludeList.push(val);
            } );
        },
        
        setUrl: function (page) {
            window.history.pushState({}, undefined, '?page=' + page);
        },
        
        loadNext: function() {
            var self = this;

            if ( !allowLoadNext )
            {
                return;
            }
            
            if ( process )
            {
                return;
            }

            utils.setNextProsessStatus(true);
            
            var ajaxOptions = {
                
                url: respunderUrl,
                dataType: 'json',
                type: 'POST',
                
                data: {
                    command: 'getNext',
                    listId: listId,
                    orderType: orderType,
                    excludeList: excludeList,
                    count: count,
                    startFrom: startPage,
                    page: endPage + 1
                },
                
                beforeSend: function()
                {
                    self.showPreloader('d');
                },
                
                success: function(data)
                {
                    utils.setNextProsessStatus(false);
                    
                    if ( !data.items || data.items.length == 0 )
                    {
                        allowLoadNext = false;
                    }
                    else
                    {
                        endPage = endPage + 1; 
                        
                        allowLoadNext = true;
                        self.addToExcludeList(data.items);
                        self.renderNextList(data.content);

                    }
                    
                    self.changeUrl();
                    self.hidePreloader();
                }
            };

            $.ajax(ajaxOptions);
        },
        
        loadPrevious: function() {
            var self = this;

            if ( !allowLoadPrev || (startPage) < 2 )
            {
                return;
            }

            if ( process )
            {
                return;
            }

            utils.setNextProsessStatus(true);
            
            var ajaxOptions = {
                
                url: respunderUrl,
                dataType: 'json',
                type: 'POST',
                
                data: {
                    command: 'getPrev',
                    listId: listId,
                    orderType: orderType,
                    excludeList: excludeList,
                    count: count,
                    startFrom: startPage - 1,
                    page: startPage - 1
                },
                
                beforeSend: function()
                {
                    self.showPreloader('up');
                },
                
                success: function(data)
                {
                    utils.setNextProsessStatus(false);
                    
                    startPage = startPage - 1;
                    
                    if ( startPage < 2 )
                    {
                        allowLoadPrev = false;
                        prevLinkNode.hide();
                    }
                    else
                    {
                        allowLoadPrev = true;
                    }
                    

                    if ( !data.items || data.items.length == 0 )
                    {
                    }
                    else
                    {
                        self.addToExcludeList(data.items);
                        self.renderPrevList(data.content);
                    }
                    
                    self.changeUrl();
                    self.hidePreloader();
                }
            };

            $.ajax(ajaxOptions);
        },
        
        showPreloader: function( position )
        {
            var self = this;
            
            if ( position == 'up' )
            {
                prevLinkNode.addClass('ow_preloader');
                //self.renderPrevList(preloader);
            }
            else
            {
                self.renderNextList(preloader);
            }
        },
        
        hidePreloader: function()
        {
            preloader.detach();
            prevLinkNode.removeClass('ow_preloader');
        },
        
        renderNextList: function(content)
        {
            contentNode.append(content);
        },
        
        renderPrevList: function(content)
        {
            contentNode.prepend(content);
            //contentNode.prepend(content);
        },
        
        tryLoadData: function()
        {
            var self = this;

            if ( !allowLoadNext )
                return;

            var diff = $(document).height() - ($(window).scrollTop() + $(window).height());

            if ( diff < 100 )
            {
                self.loadNext();
            }
        },
        
        changeUrl: function()
        {
            var self = this;
            
            var list = $('.memberx_search_result_page');

            list.sort(function( a, b ) {  
                var p1 = $(a).data('page');
                var p2 = $(b).data('page');
                
                if ( p1 > p2 )
                {
                    return -1;
                }
                else if ( p1 < p2 )
                {
                    return 1;
                }
                
                return 0;
            } );

            var height = $(window).scrollTop() + $(window).height()/2;
            var page;
            $.each( list, function( key, item ) {
                var node = $( item );
                var offset = node.offset();
                page = node.data('page');
                
                if ( offset.top < height )
                {   
                    return false;
                }
            } );
            
            if ( page )
            {
                self.setUrl(page);
            }
        }
    } 
})(jQuery);