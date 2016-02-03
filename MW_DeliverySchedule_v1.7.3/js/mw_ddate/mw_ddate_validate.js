/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * validate format Time_Interval
 */
Validation.add(
    'validate-time-interval', 
    'Please enter in valid format. Example 8:30-9:30',
    function (v){
        if(Validation.get('IsEmpty').test(v)) return true;
        result = /^[0-9]{1,2}:[0-9]{0,2}-[0-9]{1,2}:[0-9]{0,2}$/.test(v);
        if(result){
            hours = v.match(/(\d+):/g);
            for(index = 0; index < hours.length; index++){
                hours[index] = hours[index].substring(0, hours[index].length -1);
                if (parseInt(hours[index]) > 24) return false;
            }
            
            minutes = new Array();
            array = /:\d+-/.exec(v);
            if(array == null){
                minutes.push('0');
            }else{
                minutes.push(array[0]);
            }

            array = /:\d+$/.exec(v);
            if(array == null){
                minutes.push('0');
            }else{
                minutes.push(array[0]);
            }
            
            for(index = 0; index < minutes.length; index++){
                if(parseInt(minutes[index]) == 0){
                    minutes[index] = minutes[index][0].substring(1, minutes[index][0].length);
                }   
                if (parseInt(minutes[index]) > 60) return false;
            }

            if(parseInt(hours[0]) > parseInt(hours[1])){
                return false;
            }else if(parseInt(hours[0]) == parseInt(hours[1])){
                if(parseInt(minutes[0]) > parseInt(minutes[1])){
                    return false;
                }
            }
        }
        return result;
    }
);

/**
 * validate format Special_Day
 */
Validation.add(
    'validate_special_day',
    'Please enter in valid format. Example:11-8;9-2 or 2011-11-8;2011-9-2',
    function(v){
        if(Validation.get('IsEmpty').test(v)) return true;
        else{
            position = v.search(';');
            if(typeof(position) == "undefined"){
                specialDays = array();
                specialDays.push[v];
            }else{
                specialDays = v.split(";");
            }
            
            for(index=0; index< specialDays.length; index++){
                if(/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/.test(specialDays[index])){
                    month = /-[0-9]{1,2}-/.exec(specialDays[index]);
                }else if(/^[0-9]{1,2}-[0-9]{1,2}$/.test(specialDays[index])){
                    month = /^[0-9]{1,2}-/.exec(specialDays[index]);
                }else{
                    return false;
                }
                day = /[0-9]{1,2}$/.exec(specialDays[index]);
                month = /[0-9]{1,2}/.exec(month);
                day = /[0-9]{1,2}/.exec(day);
                day = parseInt(day);
                if((parseInt(month) > 12) || (parseInt(day) > 31) || (parseInt(day) == 0)){
                    return false;
                }
            }
            
            return true;
        }
    }
);
