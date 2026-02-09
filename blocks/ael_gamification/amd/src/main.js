
 define(
    [
        'jquery',
        'core/notification',
        'core/templates',
        'core/ajax',
        'core/str'
    ],
    function(
        $,
        Notification,
        Templates,
        Ajax,
        Str
    ) {
        var SELECTORS = {
            LOADER:'.loader',
            RESPONSECARD:'.ael_gamification_label_card',
            PROGRESSBAR:'.ael_gamification_pb_bar_load',
            RANCKING:'#container_rancking',
            TABBADGES:'ael_tab1',
            TABRANCKING:'ael_tab2',
            TAB:'.ael_gamification_tab',
            VIEWMAIN:'#ael_view',
            BADGEGET:'.container-badges-get',
            BADGENOGET:'.container-badges-noget',
            TABSRANCKING:'.ael_gamification_view_rackings_tab',
            SELECTEDTABRANCKING:'selected_tab_racking',
            IMAGCONTAINERBADGE:'.ael_gamification-icon-container'
            
        }

  
    

     
      
        var init = function(userid, root) {
            root = $(root);
           
         


        };

  
        /*
            typeCard = POINT|RANKING|MEDAL|PROGRESS
        */
        const _cardInfo = (root,typeCard)=>{
         
            const KETMETHOD = typeCard.toLowerCase();
          
            const request = {
            methodname: `ael_gamification_get_${KETMETHOD}`,
                args: {
                    param:''
                },
            };
            Ajax.call([request])[0].then(function(response) {
                setLoader(root,false);
                if(typeCard === 'POINT'){
                    callBackPoint(root,response);
                }
                if(typeCard === 'RANKING'){
                    callBackRancking(root,response);
                }

                if(typeCard === 'MEDAL'){
                    callBackMedal(root,response);
                }

                if(typeCard === 'PROGRESS'){
                    callBackProgress(root,response);
                }
                
               
            }).catch(Notification.exception);
            
        }


        const callBackPoint  = (root,points)=>{
          
            (async()=>{
                const label = await getLabel('point_label',points);
                const response = `${label}`;
                setResponseCard(root,response);
            })();
       
        };

        const callBackRancking  = (root,position)=>{
           
            (async()=>{
                const label = position ? await getLabel('ranking_label',position) : await getLabel('ranking_label_nodata');
                const response = `${label}`;
                setResponseCard(root,response);
            })();
       
        };


        const callBackMedal  = (root,value)=>{
            
            (async()=>{
                const label = await getLabel('medal_label');
             
                setResponseCardBadge(root,value,label);
            })();
       
        };

        const callBackProgress  = (root,value)=>{
             const num = $(root).find('h4')[1];
             const prgressBar = $(root).find(SELECTORS.PROGRESSBAR)[0];
            const perc = value+'%';
             $(prgressBar).css("width",perc);
             $(num).html(perc);
             
      
       
        };




        const setLoader = (root,visible) => {
            const spanLoader = $(root).find(SELECTORS.LOADER);
            if(!spanLoader || spanLoader.length === 0) return;
        
            visible ? $(spanLoader[0]).show() : $(spanLoader[0]).hide();
        };

        const setResponseCard = (root,response) => {
            const p = $(root).find(SELECTORS.RESPONSECARD);
            if(!p || p.length === 0) return;

            $(p).html(response);
        };



        const setResponseCardBadge = (root,response,label) => {
            const imgC = $(root).find(SELECTORS.IMAGCONTAINERBADGE);
            const p = $(root).find(SELECTORS.RESPONSECARD);
            if(!p || p.length === 0) return;
           
            if(response)
                 $(imgC).html(response);

            $(p).html(label);
            
        };

       
        
        const getLabel = async (key,params)=>{
         
            return  await Str.get_string(key,'block_ael_gamification',params);
        };



        const _initTabsEvent  = (root)=>{
            const URLTAB = window.location.hash;
            
            $( root ).on( "click", SELECTORS.TAB, loadView.bind(this) );

            if(URLTAB.includes('tab1')){
                $("#"+SELECTORS.TABBADGES).click();
            }else{
                $("#"+SELECTORS.TABRANCKING).click();
            }
       
          
          
           
        };

        const loadView = (e)=>{
            $(SELECTORS.VIEWMAIN).html('');
            const id = e.target.id
            let view = 'ranckings';
            let tab = 'tab2';
            if(id === SELECTORS.TABBADGES) {
                view = 'badges';
                tab = 'tab1';
            }
            window.location.hash = tab;
           
            $(SELECTORS.TAB).removeClass('selected_tab');
            $(e.target).addClass('selected_tab');
            
            Templates.render(`block_ael_gamification/view/${view}`, {}).then((html,js)=>{

                Templates.replaceNodeContents( $(SELECTORS.VIEWMAIN),html,js)
             
            })

        };



        const _initViewBadges = (root)=>{           
            const containerBadgeGet =  $(root).find(SELECTORS.BADGEGET);
            const containerBadgeNoGet =  $(root).find(SELECTORS.BADGENOGET);
            const request = {
            methodname: `ael_gamification_get_badges`,
                args: {
                    param:''
                },
            };
            Ajax.call([request])[0].then(function(data) {
                $(containerBadgeGet).html('');
                $(containerBadgeNoGet).html('');
                if(data && data.get && data.noget){
                    var promisesGet =  data.get.map(badge =>{
                        return  Templates.render('block_ael_gamification/components/badge', badge);
                       });

                    var promisesNoGet = data.noget.map(badge =>{
                    return  Templates.render('block_ael_gamification/components/badge', badge);
                    });

                    setBadgesContainer(promisesGet,containerBadgeGet);
                    setBadgesContainer(promisesNoGet,containerBadgeNoGet);

                    if( data.noget.length  == 0){
                        Templates.render('block_ael_gamification/components/nobadges',{}).then((html)=>{
                        
                            $(containerBadgeNoGet).html(html);
                        })
                    
                    }
                    if( data.get.length  == 0){
                        Templates.render('block_ael_gamification/components/nobadges',{}).then((html)=>{
                            $(containerBadgeGet).html(html);
                        })
                    
                    }
                    
                }
               
             

               
            }).catch(Notification.exception);
           
        } 

        

        const setBadgesContainer = (badgesPromise,container)=>{
            $.when.apply(null, badgesPromise).then(function() {
                badgesPromise.forEach(function(promise) {
                    promise.then(function(html) {
                        $(container).append(html);
                        return;
                    })
                    .catch(Notification.exception);
                });
            });
        }


        // TAB_LEFT TAB_CENTER TAB_RIGHT
        const _initRanking = ()=>{
            
            

            $(SELECTORS.TABSRANCKING).bind('click',(e)=>{
                const elm = $(e.target);
                $(SELECTORS.TABSRANCKING).removeClass(SELECTORS.SELECTEDTABRANCKING);
                elm.addClass(SELECTORS.SELECTEDTABRANCKING);
                const typeTab = elm.attr('data-tab');
                callRacking($('#ranking_left'),typeTab,'left');
                callRacking($('#ranking_right'),typeTab,'right');
                

            })
                $($(SELECTORS.TABSRANCKING)[0]).click();
     
        
           

           

    
        }
        const callRacking = (root, tab, typeRancking)=>{
            setLoader(root,true);
        
            const container =  $(root).find(SELECTORS.RANCKING);
            $(container).html('');
    
            const request = {
            methodname: `ael_gamification_get_rancking_type`,
                args: {
                    tab:tab,
                    type:typeRancking
                },
            };
            Ajax.call([request])[0].then(function(response) {
                
                Templates.render('block_ael_gamification/components/rancking', {users:response}).then((html)=>{
                    setLoader(root,false);
                    $(container).html( html);
                })
               
            }).catch(Notification.exception);

           

    
        }

        const _setAvatar = (enabled,link)=>{
            const a = $('.ael_gamification-avatar-container').find("a");
            if(enabled != 0) $(a).attr('href',link);

            const img = $(a).find('img');
            const time = new Date().getTime();
            const src = $(img).attr('src');
            $(img).attr('src',`${src}?t=${time}`);

            $('.ael_gamification-avatar-container').on('click',()=>{
                location.href = $(a).attr('href');
                
            })
        }
    

        return {
            init: init,
            cardInfo:_cardInfo,
            initViewBadges:_initViewBadges,
            initRanking:_initRanking,
            initTabsEvent:_initTabsEvent,
            setAvatar:_setAvatar

        };
    });
