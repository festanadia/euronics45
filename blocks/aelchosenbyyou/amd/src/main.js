 define(
    [
        'jquery',
        'core/ajax',

    ],
    function(
        $,
        Ajax,
        
    ) {

        const getValuesCategory = (id) => {

            const request = {
            methodname: 'ael_chosenbyyou_get_category',
                args: {
                    'options': {'ids': id},
                },
            };

            return Ajax.call([request])[0];
        };


        return {
            getValuesCategory: getValuesCategory
        };
    });

