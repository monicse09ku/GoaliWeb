<script>
    $('.datepicker').datepicker({
        format: 'mm/dd/yyyy'
    });

    $(document).on('keypress', '.price', function(){
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    function isNumberKey(txt, evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode == 46) {
            //Check if the text already contains the . character
            if (txt.value.indexOf('.') === -1) {
                return true;
            } else {
                return false;
            }
        } else {
            if (charCode > 31 &&
                (charCode < 48 || charCode > 57))
                return false;
        }
        return true;
    }

    function getFormattedDate(original_date,format=''){
        const dayNames = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
            "Sunday"
        ];
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];

        //original_date = original_date.replace(' ', 'T');
        var formattedDate = new Date(original_date);

        var d = formattedDate.getDate();
        var day = formattedDate.getDay();
        var m =  formattedDate.getMonth();
        m += 1;  // JavaScript months are 0-11
        var y = formattedDate.getFullYear();
        var hours = formattedDate.getHours();
        var minutes = formattedDate.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;

        if(original_date=='' || original_date===null){
            return '';
        }
        if(format==''){
            return (d + '/' + m + '/' + y + ' ' + hours + ':' + minutes +' '+ampm);
        }
        else if(format=='m/d/Y'){
            if(d<10){
                d = '0'+d;
            }
            if(m<10){
                m = '0'+m;
            }
            return m + "/" + d + "/" + y;
        }
        else if(format=='d/m/Y'){
            if(d<10){
                d = '0'+d;
            }
            m = m+1;
            if(m<10){
                m = '0'+m;
            }
            return d + "/" + m + "/" +  y;
        }
        else if(format=='y-m-d'){
            if(d<10){
                d = '0'+d;
            }
            //m = m+1;
            if(m<10){
                m = '0'+m;
            }
            return y + "-" + m + "-" + d;
        }
        else if(format=='M d'){
            return monthNames[m-1] + " " + d;
        }
        else if(format=='M-d-y'){
            return monthNames[m-1] + "-" + d + "-" + y;
        }
        else if(format=='l M d, Y h:i a'){
            return (dayNames[day] + ' ' + monthNames[m-1] + ' ' + d +', ' + y + ' ' + hours + ':' + minutes +' '+ampm);
        }
        else if(format=='m d, y'){
            return monthNames[m-1] + " " + d + ", " + y;
        }
        else{
            return m + "/" + d + "/" + y;
        }
    }

    function getDateDifference(start_date, end_date, difference_in){
        // To calculate the time difference of two dates
        var Difference_In_Time = start_date.getTime() - end_date.getTime();

        if(difference_in=='year'){
            //
        }
        else if(difference_in=='month'){
            //
        }
        else if(difference_in=='week'){
            //
        }
        else if(difference_in=='day'){
            // To calculate the no. of days between two dates
            var Difference = Difference_In_Time / (1000 * 3600 * 24);
        }
        else if(difference_in=='hour'){
            //
        }
        else if(difference_in=='minute'){
            //
        }
        else{
            // To calculate the no. of days between two dates
            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
        }

        return Difference;
    }

</script>
