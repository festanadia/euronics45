

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
        Str,
        Swiper
        
    ) {

       
        var SELECTORS = {
            LOADER:'.loader',
            PERCPROGRESS :'.ael_training_num_progress',
            BARPROGRESS:'.ael_training_pb_bar_load',
            CONTAINERFILTERPRIMARY:'.ael_training_container_filters_primary',
            CONTAINERFILTERSECONDARY:'.ael_training_container_filters_secondary',
            FILTER:'.ael_training_filter',
            FILTER_SECONDARY:'.ael_training_filter_secondary'
            
        }

        var DATACOURSES = {

            START:[],
            INPROGRESS:[],
            FINISH:[]
         }

         var ROOT;


        const FILTERSDATA = {

            primaryFilter:[
                {
                    label:'IN CORSO',
                    num:0,
                    key:'INPROGRESS'
                },
                {
                    label:'DA COMPLETARE',
                    num:0,
                    key:'START'
                },
                {
                    label:'CONCLUSI',
                    num:0,
                    key:'FINISH'
                }
            ]
        }




    
    
        const _initProgressBar = (root)=>{
           
            const request = {
                methodname: `ael_training_get_progress`,
                    args: {
                        param:''
                    }
                };
                Ajax.call([request])[0].then(function(perc) {
                    setPercProgressBar(root,perc);
                  
                       
                   
                }).catch(Notification.exception);
            
            
        };

        const setPercProgressBar = (root,perc)=>{
            const percContainer = $(root).find(SELECTORS.PERCPROGRESS);
            const barProgress = $(root).find(SELECTORS.BARPROGRESS);
            $(percContainer).html(`${perc}%`);
            const leftStyle = `calc(${perc}% - 24px)`;
            $(percContainer).css('left',leftStyle);
            $(barProgress).css('width',`${perc}%`);
        };


       const  loadCoursesSlider = (courses)=>{
           const container = ROOT.find('#ael_training-slides');
            Templates.render(`block_ael_training/components/carousel`, {courses}).then((html,js)=>{

                Templates.replaceNodeContents(container,html,js)
            
            })
        };
        const  loadCoursesView = (courses)=>{
            const container = ROOT.find('#ael_training-courses-view');
             Templates.render(`block_ael_training/components/list-course`, {courses}).then((html,js)=>{
 
                 Templates.replaceNodeContents(container,html,js)
             
             })
         };
 

 
       const _init = (root)=>{
              ROOT = root;
              const containerPrimary =  root.find(SELECTORS.CONTAINERFILTERPRIMARY);
              
              getAllCourses(()=>{
                FILTERSDATA.primaryFilter[0].num = DATACOURSES.INPROGRESS.length;
                FILTERSDATA.primaryFilter[1].num = DATACOURSES.START.length;
                FILTERSDATA.primaryFilter[2].num = DATACOURSES.FINISH.length;

                Templates.render(`block_ael_training/components/filters-primary`, {filters:FILTERSDATA.primaryFilter}).then((html,js)=>{
                    Templates.replaceNodeContents($(containerPrimary),html,js);
                    setEventClickPrimaryFilter();

                })
              });
            
        };

 
     
        

        const setEventClickPrimaryFilter  = ()=>{
            const selectorFilter = `${SELECTORS.CONTAINERFILTERPRIMARY} ${SELECTORS.FILTER}`;
            $(selectorFilter).bind('click',(e)=>{
                const key = e.target.dataset.key;

                $(selectorFilter).removeClass('ael_training_filter_active');
                e.target.classList.toggle("ael_training_filter_active");

                if(DATACOURSES[key]){
                   const {categories,all} = setCategoryCourse(DATACOURSES[key]);
                   CURRENTCATEGORYANDCOURSES = categories;
                   setCategoryFilter(categories,all);
                }

               
            })
            
            let idx = FILTERSDATA.primaryFilter.findIndex( f => f.num > 0);
            
            $($(`${SELECTORS.CONTAINERFILTERPRIMARY}`).find(SELECTORS.FILTER)[idx]).click();
        };

        const setCategoryFilter = (categories,all)=>{
            
            const container = $(SELECTORS.CONTAINERFILTERSECONDARY);
            Templates.render(`block_ael_training/components/filters-secondary`, {filters:categories,all}).then((html,js)=>{
                Templates.replaceNodeContents($(container),html,js);
                setEventClickSecondaryFilter(categories,all);
             
                
            })
        };

        const setEventClickSecondaryFilter  = (categories,all)=>{
            const selectorFilter = `${SELECTORS.CONTAINERFILTERSECONDARY} ${SELECTORS.FILTER_SECONDARY}`;
            $(selectorFilter).bind('click',(e)=>{
                const key = e.target.dataset.key;

                $(selectorFilter).removeClass('ael_training_filter_active');
                e.target.classList.toggle("ael_training_filter_active");
                let coursers = all.courses;

                if(key != 'all'){
                    const category =  categories.find( elm => elm.coursecategory === key);
                    coursers = category.courses
                }

                if(ROOT.attr('id').includes('view')){
                    loadCoursesView(coursers);
                }else{
                    loadCoursesSlider(coursers);
                }
               
             
            })
            $($(`${SELECTORS.CONTAINERFILTERSECONDARY}`).find(SELECTORS.FILTER_SECONDARY)[0]).click();
        };


        const setCategoryCourse = (courses)=>{
            const category = [];
            courses.map((course)=>{
                let idx = category.findIndex((c)=> c.coursecategory === course.coursecategory);
              
                if(idx === -1){
                    category.push({
                        coursecategory : course.coursecategory,
                        num : 1,
                        courses:[course]
                    })
                }else{
                    
                    category[idx].num += 1;
                    category[idx].courses.push(course);
                }
                
            });
            return {categories: category ,all:{courses , num:courses.length}} ;
        }

        


        const getAllCourses = (callback)=>{
            const request = {
                methodname: `ael_training_get_all_courses`,
                    args: {
                        param:''
                    }
                };
                Ajax.call([request])[0].then(function(response) {
                    response.map((course)=>{
                        DATACOURSES[course.status].push(course);
                    })

                    callback();
                       
                   
                }).catch(Notification.exception);
        }

        return {
            initProgressBar:_initProgressBar,
            init:_init

        };
    });
