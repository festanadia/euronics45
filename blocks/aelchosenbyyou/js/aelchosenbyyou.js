
    var elem = document.getElementById("slider-0");
    if ( elem != null ) elem.classList.add('active');
    else{
        document.getElementById("arrow").style.display = "none";
    }
    elem = document.getElementById("indicator-0");
    if ( elem != null ) elem.classList.add('active');
    else{
        document.getElementById("arrow").style.display = "none";
    }
    $(".carousel").swipe({

        swipe: function(event, direction, distance, duration, fingerCount, fingerData) {

            if (direction == 'left') $(this).carousel('next');
            if (direction == 'right') $(this).carousel('prev');

        },
        allowPageScroll:"vertical"
    });

    // variable num of elements for groups
    var num = 0;
    hiddenIndicator(num);


    function hiddenIndicator(num){
        if ( num == 0 ) num = 1;
        var x = document.getElementsByClassName("indicatorClass");
        for(i=0; i<x.length; i++){
            const chars = x[i].id.split('-');
            if((chars[1]%num)!=0) $("#"+x[i].id).hide();
        }
    }