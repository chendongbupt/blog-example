// 定时器插件
// 需要传入 startTime endTime 至少一个要大于0
// formmat : 格式  
//           当为字符串 'Object' 时， 直接返回数据对象
//			 否则需要在字符串中有 {D} {H} {i} {s} 4个参数, 可以灵活设置返回的字符串格式，设置dom结构
//			  selectorId 为 string 时， 返回字符串， 为其他时，直接操作dom字符串

(function($){ 
    $.fn.extend({ 
    	timer: function(options){ 
            var defaults = { 
            		startTime : 0,
            		endTime : 0,
            		format : '{D}:{H}:{i}:{s}',  //返回格式
            		interval: 1000,              //间隔
            		selectorId : 'timer',        // dom id
            		way : 'cycle'                // 是否循环
            	},
            	interName = 0,
            	isCycle = true,
            	curTime = parseInt(new Date().getTime() / 1000);

            options = $.extend(defaults, options);

            if ( !checkOptions() ){ 
            	return false;
            }
            if ( options.way !== 'cycle' )
            	isCycle = false;

			console.log('startshow');
        	startShowTime();

        	function checkOptions(){ 
        		if ( options.startTime <= 0 && options.endTime <= 0 ){ 
        			console.log('请设置时间参数 startTime endTime');
        			return false;
        		}

        		if ( !options.format.match(/{D}.*{H}.*{i}.*{s}/g) && options.format !== 'Object' )  { 
        			console.log('format 格式不对， 需要包含 {D} {H} {i} {s}');
        			return false;
        		}

        		if ( options.selectorId !== 'string' && $('#'+options.selectorId).length <= 0 && options.format !== 'Object') { 
                   console.log(' selctorId 不存在');
                   return false;
        		}

    			return true;
        	}

            function startShowTime(){
			    if ( !isCycle )
			    	interName = setTimeout(function(){showTime();}, options.interval);
			    else {
			    	interName = setInterval(function(){showTime();} , options.interval);
			    }
			}

			function showTime() {
			    var id = options.selectorId;

			    var curTime = parseInt(new Date().getTime() / 1000);

			    if ( curTime > options.startTime && curTime < options.endTime ){
			    	if ( options.format === 'Object' || id === 'string' )
			    		return calcTime(options.endTime, curTime);
			    	else
			        	document.querySelector('#'+id).innerHTML = calcTime(options.endTime, curTime);
			        
			        	
			    }
			    else if ( curTime <= options.startTime ){
			    	
			        if ( options.format === 'Object' || id === 'string' ){ 
			        	console.log(calcTime(options.startTime, curTime))
			    		return calcTime(options.startTime, curTime);
			    	}
			        else
			        	document.querySelector('#'+id).innerHTML = calcTime(options.startTime, curTime);
			    }
			    else{
			    	if ( id === 'string' )
			    		return '活动结束';
			        else 
			        	document.querySelector('#'+id).innerHTML = '活动结束！';

			        if ( isCycle )  
			        	clearInterval(interName);
			    }

			   
			}

			function calcTime(end, start){
			    var lefttime = end - start,
			        leftSeconds = lefttime % 60 ,
			        leftMins = Math.floor(lefttime % 3600 / 60)  ,
			        leftHours = Math.floor(lefttime % (3600 * 24) / 3600 ) ,
			        leftDays = Math.floor(lefttime / (3600 * 24)) ;

			    if ( options.format === 'Object' ){ 
			    	return { 
			    		day: leftDays,
			    		hour: leftHours,
			    		min: leftMins,
			    		sec: leftSeconds,
			    		time: lefttime
			    	}
			    }
			    
			    var timer = options.format;
			    timer = timer.replace(/{D}/g, leftDays);
			    timer = timer.replace(/{H}/g, leftHours);
			    timer = timer.replace(/{i}/g, leftMins);
			    timer = timer.replace(/{s}/g, leftSeconds);
			   
			    return timer;
			}
    	}
    })
})(jQuery)