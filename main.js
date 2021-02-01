var CurrentlyUsingFile = "";

function setDatePickers(objDate) {

    objDate.position = 'top left';
    objDate.reposition = false;
    objDate.format = 'DD-MM-YYYY';

    new Pikaday(objDate);
}


function convertToDate(sString) {
    var dateString = sString; // Oct 23

    var dateParts = dateString.split("-");

    // month is 0-based, that's why we need dataParts[1] - 1
    var dateObject = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]);

    return dateObject
}

function generateChart(lowerBound, upperBound, mean, stdDev)
{
    let min = lowerBound - 2 * stdDev;
    let max = upperBound + 2 * stdDev;
    let unit = (max - min) / 100;
    let points =  _.range(min, max, unit);   

    let seriesData = points.map(x => ({ x, y: normalY(x, mean, stdDev)}));
    
    Highcharts.chart('container', {
        chart: {
            type: 'area'
        },
        series: [{
            data: seriesData,
        }],
    });
}

$('#upload').on('click', function (event) {
    event.preventDefault();
    event.stopPropagation();
    var file_data = $('#IdUploadCsv').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    $.ajax({
        url: 'support/upload.php', // point to server-side PHP script 
        dataType: 'json', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: 'post',
        success: function (objResponse) {
            if (objResponse != null && objResponse.hasOwnProperty("Status")) {
                if (objResponse.Status == 200) {
                    
                    $('#CSVUploadModal').modal('hide');
                    CurrentlyUsingFile = objResponse.FileName;
                    var dtFromDate = convertToDate(objResponse.MinDate);
                    var dtToDate = convertToDate(objResponse.MaxDate);                    

                    setDatePickers({
                        field: document.getElementById('dtFrom'),
                        minDate: dtFromDate,
                        maxDate: dtToDate,
                        defaultDate: dtToDate
                    });
                    setDatePickers({
                        field: document.getElementById('dtTo'),
                        minDate: dtFromDate,
                        maxDate: dtToDate,
                        defaultDate: dtToDate
                    });

                    document.getElementById('idFromDateText').innerHTML = objResponse.MinDate;
                    document.getElementById('idToDateText').innerHTML = objResponse.MaxDate;

                    for (iIndex in objResponse.AvailableCompany) {
                        $("#idCompanyTrack").append("<option>" + objResponse.AvailableCompany[iIndex] + "</option>");
                    }

                } else {
                    alert(objResponse.Msg);
                }
            } else {
                alert("Internet issue");
            }

            // alert(php_script_response); // display response from the PHP script, if any
        }
    });
});


$("#AnalysData").submit(function (event) {
    event.preventDefault();
    event.stopPropagation();

    var $form = $(this);

    // Serialize the data in the form
    var serializedData = $form.serialize() + '&FileName=' + CurrentlyUsingFile  +".csv";

    request = $.ajax({
        url: "support/index.php",
        type: "post",
        data: serializedData,
        // contentType: "application/json",
        success: function (objRespo) {

            var objResponse = JSON.parse(objRespo.toString());
            
            if(objResponse != null)
            {
                
                if(objResponse.Stat != null)
                {     
                    let lowerBound = objResponse.MinDate;
                    let upperBound = objResponse.MaxDate;
                    let mean = objResponse.Stat.Mean;
                    let variance = objResponse.Stat.Variance;
                    let stdDev = objResponse.Stat.SD;
    
                    document.getElementById('SD').innerHTML = objResponse.Stat.SD;
                    document.getElementById('Mean').innerHTML = objResponse.Stat.Mean;
    
                    // generateChart(lowerBound, upperBound, mean, stdDev);
                }

                document.getElementById('Suggestion').innerHTML = objResponse.Conclusion;
                document.getElementById('Invest').innerHTML = objResponse.Invest;
                document.getElementById('TurnOver').innerHTML = objResponse.TurnOver;
                document.getElementById('Profit').innerHTML = objResponse.Profit;

                for(i = 0; i < objResponse.Data.length(); i++)
                {
                    $("#ActionTable > tbody").append("<tr>"
                    + '<th scope="col">'+ objResponse.Data["date"] +'</th>'
                    + '<th scope="col">'+ objResponse.Data["stock_name"] +'</th>'
                    + '<th scope="col">'+ objResponse.Data["price"] +'</th>'
                    + '<th scope="col">'+ objResponse.Data["Action"] +'</th>'
                    + "</tr>");
                }
            }
        }
    });
});

$('#CSVUploadModal').modal({
    backdrop: 'static', keyboard: true
})