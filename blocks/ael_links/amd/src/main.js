

 define(
    [
        'jquery',
        'core/notification',
        'core/templates',
        'core/ajax',
        'core/str',
    ],
    function(
        $,
        Notification,
        Templates,
        Ajax,
  
        
    ) {

       
        var SELECTORS = {
            LOADER:'.loader',
            CONTAINER :'.ael_links_container_slider',
            CARD:'.ael_links-container-data'
            
            
        }

      




    
    
        const _initCourses = (root,param)=>{
           
            const request = {
                methodname: `ael_links_get_all_courses`,
                    args: {
                        ...param
                    }
                };
                Ajax.call([request])[0].then(function(courses) {

                    loadCoursesSlider(root,courses);
                  
                       
                   
                }).catch(Notification.exception);
            
            
        };

    


       const  loadCoursesSlider = (root,courses)=>{
           const container = root.find(SELECTORS.CONTAINER);
            if(courses && courses.length > 0){
               
                Templates.render(`block_ael_links/courses`, {courses}).then((html,js)=>{
                    Templates.replaceNodeContents(container,html,js);

                    const cards = container.find(SELECTORS.CARD);
                    
                    $.each(cards,(_,card)=>{
                        $(card).on('click',()=>{
                            const link = $(card).attr('data-url');
                            location.href = link;
                           
                        })
                    })
                })
            }else{
                Templates.render(`block_ael_links/nocourses`, {courses}).then((html,js)=>{

                    Templates.replaceNodeContents(container,html,js)
                
                })

            }
            
        };
      
 

 
     

        return {
            initCourses:_initCourses

        };
    });
