

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
            CONTAINER :'.ael_evidence_container_slider',
            
            
        }

      




    
    
        const _initCourses = (root,param)=>{
           
            const request = {
                methodname: `ael_evidence_get_all_courses`,
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
               
                Templates.render(`block_ael_evidence/courses`, {courses}).then((html,js)=>{
                    $(container).attr('style','background-image:url("'+$(container).attr('data-img')+'")');
                    Templates.replaceNodeContents(container,html,js)
                })
            }else{
                Templates.render(`block_ael_evidence/nocourses`, {courses}).then((html,js)=>{
                    $(container).attr('style','height:auto')
                    Templates.replaceNodeContents(container,html,js)
                
                })

            }
            
        };
      
 

 
     

        return {
            initCourses:_initCourses

        };
    });
