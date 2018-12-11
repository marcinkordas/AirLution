<?php
if ($dwmyChoose == 0) $timeDependentVariable = 24;
else if ($dwmyChoose == 1) $timeDependentVariable = 7;
else if ($dwmyChoose == 2) $timeDependentVariable = 31;
else if ($dwmyChoose == 3) $timeDependentVariable = 12;

$showAlert = false;
?>

<script src="https://www.gstatic.com/firebasejs/5.5.9/firebase.js"></script>
<script>
function zeros(dimensions)
{
  var array = new Array();
  for (var i=0;i < dimensions[0];++i)
  {
    array.push(dimensions.length == 1 ? 0 : zeros(dimensions.slice(1)));
  }
  return array;
}
var counterSizeDependingOnDates = <?php echo $timeDependentVariable; ?>;


  // Initialize Firebase
  // DONETODO: Replace with your project's customized code snippet
  var config = {
    apiKey: "AIzaSyBFaACiRItyaK5V61o26KLhCYlsS-G6Boc",
    authDomain: "eit-siu-iot.firebaseapp.com",
    databaseURL: "https://eit-siu-iot.firebaseio.com",
    projectId: "eit-siu-iot"
  };
  firebase.initializeApp(config);

  //Create arrays of readings
  var sensorNames = [4];
  var sensorReadings = [[],[],[],[]];
  var f1 = firebase.database().ref();
  f1.on('value', function(datasnapshot){
    //console.log(datasnapshot.val());
    var sensorIndex = 0;
    for (sensor in datasnapshot.val())
    {
      sensorNames[sensorIndex] = sensor.toString();
      var f = firebase.database().ref().child(sensor.toString());
      f.on('value', function(snap2){
        var readingIndex = 0;
        for (reading in snap2.val())
        {
          sensorReadings[sensorIndex][readingIndex++] = reading.toString();
        }
      });
      sensorIndex++;
    }


//fetch from all sensors
var fullData = [];
var definedTimeBeforeNothingMatters = new Date();
definedTimeBeforeNothingMatters.setDate(definedTimeBeforeNothingMatters.getDate() - <?php
  if($dwmyChoose==0) echo '1';
  if($dwmyChoose==1) echo '7';
  if($dwmyChoose==2) echo '31';
  if($dwmyChoose==3) echo '365'; ?>);
var nowDate = new Date();
var dTime = (nowDate.getTime()-definedTimeBeforeNothingMatters.getTime())/counterSizeDependingOnDates;

for (var fetchedSensorIndex=0;fetchedSensorIndex<4;fetchedSensorIndex++)
{
  try{
    //Fetch data from one sensor
    var sumOfDataFromOneSensorForSpecificTime = zeros([counterSizeDependingOnDates,4]);
    var countOfDataInstancesFromOneSensor = 0;
    var fetchedReadingIndex = 0;
    for(var item in firebase.database().ref().child(sensorNames[fetchedSensorIndex]))
    {
      var distinctDataForOneSensor = [4];
      var timeSlot;
      var fetchFromData = firebase.database().ref().child(sensorNames[fetchedSensorIndex]).child(sensorReadings[fetchedSensorIndex][fetchedReadingIndex]).child("DataList");
      fetchFromData.on('value', function(snap){
        for (var k=0;k<4;k++) distinctDataForOneSensor[k] = snap.val()[k];
      });
      var fetchFromTimestamp = firebase.database().ref().child(sensorNames[fetchedSensorIndex]).child(sensorReadings[fetchedSensorIndex][fetchedReadingIndex]).child("Timestamp");
      fetchFromTimestamp.on('value', function(snap){
        var time = new Date(snap.val());
        if(time >= definedTimeBeforeNothingMatters)
        {
          for(var l=0;l<counterSizeDependingOnDates;l++)
          {
            if (time.getTime() >= (definedTimeBeforeNothingMatters.getTime()+l*dTime) && time.getTime() < (definedTimeBeforeNothingMatters.getTime()+(l+1)*dTime)) timeSlot = l;
          }
          for (var k=0;k<4;k++) sumOfDataFromOneSensorForSpecificTime[timeSlot][k] += distinctDataForOneSensor[k];
          countOfDataInstancesFromOneSensor++;
        }
      });
      fetchedReadingIndex++;
    }
    var averagesForOneSensor = zeros([counterSizeDependingOnDates,4]);
    for (var k=0;k<4;k++) averagesForOneSensor[timeSlot][k] = sumOfDataFromOneSensorForSpecificTime[timeSlot][k]/countOfDataInstancesFromOneSensor;
    //end of fetching from one sensor
    fullData[fetchedSensorIndex] = averagesForOneSensor;
  }catch{}
}
//end of fetching from all sensors
var dwmyChoose = <?php
  if($dwmyChoose==0) echo '0';
  if($dwmyChoose==1) echo '1';
  if($dwmyChoose==2) echo '2';
  if($dwmyChoose==3) echo '3';
?>;
var chartArray = zeros([16,counterSizeDependingOnDates]);
var PM10 = 0;
var correctReadingHappenedCounter = 0;
for (var j=0;j<16;j++)
{
  for (var i=0;i<counterSizeDependingOnDates;i++)
  {
    try{
      var tempData = fullData[Math.floor(j/4)][i][j%4];
    }catch{
      var tempData = 0;
    }
    var tempTime = new Date();
    tempTime.setTime(definedTimeBeforeNothingMatters.getTime()+dTime*i);

    if(dwmyChoose == 0) chartArray[j][i] = [tempTime.getHours()+':00', tempData];
    if(dwmyChoose == 1 || dwmyChoose == 2) chartArray[j][i] = [tempTime.getDate()+'.'+(tempTime.getMonth()+1)+'.'+(tempTime.getYear()+1900), tempData];
    if(dwmyChoose == 3) chartArray[j][i] = [(tempTime.getMonth()+1)+'.'+(tempTime.getYear()+1900), tempData];
  }
  try{
  PM10 += fullData[Math.floor(j/4)][i][j%4];
  correctReadingHappenedCounter++;
  }catch{}
}
if(correctReadingHappenedCounter!=0) PM10/=correctReadingHappenedCounter;
else PM10=0;
var alertDiv = document.getElementById("ALERT_ID");
if(PM10 < 50){
  alertDiv.setAttribute("style","color:green");
  alertDiv.innerHTML = "POZIOM ZANIECZYSZCZENIA W NORMIE";
}else if (PM10 < 200){
  alertDiv.setAttribute("style","color:orange");
  alertDiv.innerHTML = "POZIOM ZANIECZYSZCZENIA POWYŻEJ NORMY";
}else if (PM10 < 300){
  alertDiv.setAttribute("style","color:red");
  alertDiv.innerHTML = "POZIOM ZANIECZYSZCZENIA PRZEKROCZONY";
}else{
  alertDiv.setAttribute("style","color:red; font-weight:bold");
  alertDiv.innerHTML = "POZIOM ZANIECZYSZCZENIA KRYTYCZNY";
}


var locSuffix = Array(" [°C]", " [%]", " [ppm]", " [ppm]");
var location = Array("czujnika 1", "czujnika 2", "czujnika 3", "czujnika 4");
var chartDesc = Array("Temperatura", "Wilgotność", "Stężenie CO2", "Zanieczyszczenie");


  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {

    for (var j=0; j<16; j++)
    {
    // Create the data table.
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Topping');
    data.addColumn('number', chartDesc[j%4]);
    data.addRows(chartArray[j]);

    // Set chart options
    var options = {'title': chartDesc[j%4]+" dla "+location[Math.floor(j/4)]+locSuffix[j%4],
                   'height':200,
                    'legend':'none'};

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.ColumnChart(document.getElementById('chart'+(j+1)));
    chart.draw(data, options);
    }
  }

});
</script>
