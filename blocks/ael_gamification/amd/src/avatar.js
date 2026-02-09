
define(
    [
        'jquery',
        'core/notification',
        'core/templates',
        'core/ajax',
        'core/str',
        'core/config'
    ],
    function (
        $,
        Notification,
        Templates,
        Ajax,
        Str,
        mdlcfg
    ) {
        var SELECTORS = {
            BADGE: '.ael_gamification-filter-badge',
            FILETERSWIPER: '.ael_gamification-filter',
            AVATAR: '#changeavatar',
            FILTER: '.ael_gamification_data_filter',
            SELECTEDBADGE: 'ael_gamification_selected',
            BUTTONSAVE: '.savebutton-avatar',
            SPINNER: '.ael_gamification_spinner_save_avatar',
            BLOCKAVATAR: '.block_avatar'
        }


        var root;




        var initSlider = function (container) {


            const swiper = new Swiper("." + container, {
                loop: false,
                slidesPerView: 1,
                spaceBetween: 20,
                centeredSlides: true,
                observer: true,
                observeParents: true,
                navigation: {
                    nextEl: '.swiper-button-next-' + container,
                    prevEl: '.swiper-button-prev-' + container,
                }
            });


            swiper.on('slideChange', function (s) {
                const { activeIndex } = s;;
                const value = s.el.querySelectorAll(SELECTORS.FILTER)[activeIndex].getAttribute('data-value');
                window.avatardata[swiper.keyAvatar] = value;
                setImageAvatar();

            });

            const key = container.replace('ael_gamification-swiper-', '');
            const value = window.avatardata[key];
            const filters = $("." + container).find(SELECTORS.FILTER);
            window.avatarswiper[key] = $(filters[0]).attr("data-value");
            $.each(filters, (idx, f) => {
                const valueF = $(f).attr("data-value");
                if (valueF == value) {
                    swiper.activeIndex = idx;
                    window.avatarswiper[key] = valueF;
                }

            })
            swiper.keyAvatar = key;
            window.avatarslider.push(swiper);

        };

        const setImageAvatar = () => {
            const container = document.querySelector(SELECTORS.AVATAR);
            const { typeTop, accessories, clothes, eyebrow, eyes, facialhair, facialhaircolor, haircolor, mouth, skin } = window.avatardata;

            container.innerHTML = `<my-avatar  
                                    class='my-avatar'
                                    hair-color="${haircolor}" 
                                    accessories-type="${accessories}" 
                                    top-type="${typeTop}" 
                                    facial-hair-type="${facialhair}" 
                                    clothe-type="${clothes}" 
                                    eye-type="${eyes}" 
                                    eyebrow-type="${eyebrow}" 
                                    skin-color="${skin}" 
                                    facial-hair-color="${facialhaircolor}" 
                                    mouth-type="${mouth}">
                                    </my-avatar>
                                    <button type="button" class="btn btn-outline-primary ael_gamifaiction_button_random" >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                        </svg>
                                     </button>
                                    
                                    `;


            $('.ael_gamifaiction_button_random').on('click', () => {
                randomAvatar();
            })

        };

        const randomAvatar = () => {
            $.each(window.avatarslider, (idx, swiper) => {
                const numSlide = swiper.slides.length;
                swiper.slideTo(getRandomInt(numSlide))
                
            });



        }

        const getRandomInt = (max) => {
            return Math.floor(Math.random() * max);
        }


        const initBadgeEvent = function () {
            setImageAvatar();

            $(SELECTORS.FILETERSWIPER).hide();
            $(SELECTORS.FILETERSWIPER).css('visibility', 'visible');
            $(SELECTORS.BLOCKAVATAR).css('visibility', 'visible');
            $(SELECTORS.BUTTONSAVE).on('click', () => {
                saveAvatar();
            });


            $(SELECTORS.BADGE).on("click", (e) => {

                $(SELECTORS.BADGE).removeClass(SELECTORS.SELECTEDBADGE);
                $(e.target).addClass(SELECTORS.SELECTEDBADGE);
                const key = $(e.target).attr("data-value");
                $(SELECTORS.FILETERSWIPER).hide();
                $(SELECTORS.FILETERSWIPER + '-' + key).show();
                window.avatardata[key] = window.avatarswiper[key]
                setImageAvatar();

            })
        }

        const getLabel = async (key, params) => {

            return await Str.get_string(key, 'block_ael_gamification', params);
        };


        const saveAvatar = () => {
            $(SELECTORS.BUTTONSAVE).hide();
            $(SELECTORS.SPINNER).show();
            window.avatar.onDownloadPNG((blob) => {
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    const base64data = reader.result;

                    $.ajax({
                        url: mdlcfg.wwwroot + "/blocks/ael_gamification/ajax.php",
                        method: "POST",
                        data: {
                            file: base64data,
                            data: JSON.stringify(window.avatardata)

                        },

                        success: function ({ status }) {
                            $(SELECTORS.BUTTONSAVE).show();
                            $(SELECTORS.SPINNER).hide();
                            if (status) {
                                const img = $('.navbar-avatar').find('.avatar img');
                                if (img) {
                                    const src = $(img).attr('src');
                                    const time = new Date().getTime();
                                    $(img).attr('src', `${src}?t=${time}`);
                                }

                            }

                            (async () => {
                                const label = await getLabel(status ? 'save_avatar_success' : 'save_avatar_error');
                                const message = `${label}`;
                                Notification.addNotification({
                                    message,
                                    type: status ? 'success' : 'error'
                                });
                            })();

                            setTimeout(() => {
                                $('.notifications .alert').remove();
                            }, 4000)

                        },
                        error() {
                            $(SELECTORS.BUTTONSAVE).show();
                            $(SELECTORS.SPINNER).hide();
                            (async () => {
                                const label = await getLabel('save_avatar_error');
                                const message = `${label}`;
                                Notification.addNotification({
                                    message,
                                    type: 'error'
                                });
                            })();
                        }
                    });

                }



            })
        }





        return {
            initSlider: initSlider,
            initBadgeEvent: initBadgeEvent

        };
    });
