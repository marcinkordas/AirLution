<?php
if ($dwmyChoose == 0) $timeDependentVariable = 24;
else if ($dwmyChoose == 1) $timeDependentVariable = 7;
else if ($dwmyChoose == 2) $timeDependentVariable = 31;
else if ($dwmyChoose == 3) $timeDependentVariable = 12;
else if ($dwmyChoose == 4) $timeDependentVariable = 60;
else if ($dwmyChoose == 5) $timeDependentVariable = 3;
else if ($dwmyChoose == 6) $timeDependentVariable = 10;

$showAlert = false;
?>

<script src="https://www.gstatic.com/firebasejs/5.5.9/firebase.js"></script>
<script>
var dwmyChoose = <?php echo $dwmyChoose; ?>; //który okres czasu jest wybrany
var definedTimeBeforeNothingMatters = new Date(); //data początkowa dla danego okresu
var monthsTimes = new Array(); //przedziały czasowe dla roku (oddzielnie, bo odległości pomiędzy miesiącami są różne)
var dTime; //odstęp pomiędzy jedną datą, a kolejną
var counterSizeDependingOnDates = <?php echo $timeDependentVariable; ?>; //ile przedziałów czasowych ma być
var sensorNames = [4]; //nazwy sensorów
var sensorReadings = [[],[],[],[]]; //nazwy odczytów z sensorów
var sensorReadingsCount = [0,0,0,0]; //liczba odczytów dla poszczególnych sensorów
var fullData = []; //wszystkie zfetchowane dane
var chartArray = zeros([12,counterSizeDependingOnDates]); //dane przetworzone do wyświetlania w wykresach
var PM10 = 0; //poziom zanieczyszczenia z ostatniej daty/czasu, do analizy norm
var correctReadingHappenedCounter = 0; //ile razy udało się poprawnie zaczytać dane z ostatniej daty/czasu, potrzebne do średniej

// Initialize Firebase
var config = {
  apiKey: "AIzaSyBFaACiRItyaK5V61o26KLhCYlsS-G6Boc",
  authDomain: "eit-siu-iot.firebaseapp.com",
  databaseURL: "https://eit-siu-iot.firebaseio.com",
  projectId: "eit-siu-iot"
};
firebase.initializeApp(config);

//Create arrays of readings
var f1 = firebase.database().ref();
f1.on('value', function(datasnapshot){
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
        sensorReadingsCount[sensorIndex]++;
      }
    });
    sensorIndex++;
  }

DetermineTimeDifferentials(); //ustawianie przedziałów czasowych
FetchFullData(); //pobieranie danych

//konwersja FullData na ChartArray
for (var j=0;j<6;j++)
{
  for (var i=0;i<counterSizeDependingOnDates;i++)
  {
    try{
      if(j<2)
      {
        var tempData = PrepareTimeString(i);
        var tempData2 = fullData[0][i][j*2+1]-fullData[1][i][j*2+1];
        var tempData3 = NaN;
        var tempData4 = NaN;
      }
      else if (j==2)
      {
        var tempData = fullData[0][i][1];
        var tempData2 = fullData[0][i][3];
        var tempData3 = fullData[1][i][1];
        var tempData4 = fullData[1][i][3];
      }
      else if (j<5)
      {

        var tempData = PrepareTimeString(i);
        var tempData2 = fullData[2][i][Math.ceil((j*4-11)/2)]-fullData[3][i][Math.ceil((j*4-11)/2)];
        var tempData3 = NaN;
        var tempData4 = NaN;
      }
      else
      {
        var tempData = fullData[2][i][1];
        var tempData2 = fullData[2][i][3];
        var tempData3 = fullData[3][i][1];
        var tempData4 = fullData[3][i][3];
      }
    }catch{
      var tempData = NaN;
      var tempData2 = NaN;
      var tempData3 = NaN;
      var tempData4 = NaN;
    }
    if(tempData==0)tempData=NaN;
    if(tempData2==0)tempData2=NaN;
    if(tempData3==0)tempData3 = NaN;
    if(tempData4==0)tempData4 = NaN;

    chartArray[j][i] = [tempData, tempData2, null];
    chartArray[j+6][i] = [tempData3, null, tempData4];
  }
  if(!isNaN(fullData[Math.floor(j/3)][i-1][3]))
  {
    PM10 += fullData[Math.floor(j/3)][i-1][3];
    correctReadingHappenedCounter++;
  }
}

//ustawianie komunikatu z zależności od norm zanieczyszczenia
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

//zmienne opisujące wykresy
var axisNames = Array("Wilgotność [%]", "Zanieczyszczenie [ppm]"); //słownik podpisów pod osiami
var vAxisNames = Array(axisNames[0], axisNames[1], axisNames[1], axisNames[0], axisNames[1], axisNames[1]); //podpisy pod osiami y
var hAxisNames = Array(null, null, axisNames[0], null, null, axisNames[0]); //podpisy pod osiami x
var location = Array("[czujniki 1&2]", "[czujniki 3&4]"); //słownik podpisów lokalizacji (numery czujników)
var chartDesc = Array("Różnica wilgotności", "Różnica zanieczyszenia", "Zanieczyszczenie od wilgotności"); //podpisy wykresów

//mazianie wykresów
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
  for (var j=0; j<6; j++)
  {
  // Create the data table.
  var data = new google.visualization.DataTable();
  if(j%3==2)
  {
    data.addColumn('number', 'Topping');
    data.addColumn('number', 'czujnik '+Math.ceil(j/2));
    data.addColumn('number', 'czujnik '+(Math.ceil(j/2)+1));
  }
  else
  {
    data.addColumn('string', 'Topping');
    data.addColumn('number', chartDesc[j%3]);
    data.addColumn('number', chartDesc[j%3]);
  }
  data.addRows(chartArray[j]);
  if(j==2) data.addRows(chartArray[8]);
  if(j==5) data.addRows(chartArray[11]);

  // Set chart options
  var options = {
    title: chartDesc[j%3]+" "+location[Math.floor(j/3)],
    height:250,
    legend:'none',
    isStacked: true,
    hAxis: {
      title: hAxisNames[j]
    },
    vAxis: {
      title: vAxisNames[j]
    }
  };

  // Instantiate and draw our chart, passing in some options.
  if(j%3==2) var chart = new google.visualization.ScatterChart(document.getElementById('chart'+(j+1)));
  else var chart = new google.visualization.ColumnChart(document.getElementById('chart'+(j+1)));
  chart.draw(data, options);
  }
}
});


function DetermineTimeDifferentials()
{
  var nowDate = new Date();

  if(dwmyChoose==0)
  {
    definedTimeBeforeNothingMatters.setDate(definedTimeBeforeNothingMatters.getDate()-1);
    definedTimeBeforeNothingMatters.setHours(definedTimeBeforeNothingMatters.getHours()+1);
    definedTimeBeforeNothingMatters.setMinutes(0,0,0);
    nowDate.setHours(nowDate.getHours());
    nowDate.setMinutes(59,59,99);
  }
  if(dwmyChoose==1)
  {
    definedTimeBeforeNothingMatters.setDate(definedTimeBeforeNothingMatters.getDate()-6);
    definedTimeBeforeNothingMatters.setHours(0,0,0,0);
    nowDate.setHours(23,59,59,99);
  }
  if(dwmyChoose==2)
  {
    definedTimeBeforeNothingMatters.setDate(definedTimeBeforeNothingMatters.getDate()-30);
    definedTimeBeforeNothingMatters.setHours(0,0,0,0);
    nowDate.setHours(23,59,59,99);
  }
  if(dwmyChoose==3)
  {
    var tempDate = new Date();
    tempDate.setMonth(tempDate.getMonth()+1);
    for(var pushDatesIterator =0; pushDatesIterator<13; pushDatesIterator++) monthsTimes.push(new Date(tempDate));
    monthsTimes[12].setDate(tempDate.getDate());
    monthsTimes[12].setDate(0);
    monthsTimes[12].setHours(23,59,59,99);
    for(var fillDatesIterator=1;fillDatesIterator<=12;fillDatesIterator++)
    {
      monthsTimes[12-fillDatesIterator].setMonth(monthsTimes[12-fillDatesIterator].getMonth()-fillDatesIterator);
      monthsTimes[12-fillDatesIterator].setDate(0);
      monthsTimes[12-fillDatesIterator].setHours(23,59,59,99);
    }
    definedTimeBeforeNothingMatters.setMonth(tempDate.getMonth()-12);
    definedTimeBeforeNothingMatters.setDate(0);
    definedTimeBeforeNothingMatters.setHours(23,59,59,99);
  }
  if(dwmyChoose==4)
  {
    definedTimeBeforeNothingMatters.setMinutes(definedTimeBeforeNothingMatters.getMinutes()-1);
  }
  if(dwmyChoose==5)
  {
    definedTimeBeforeNothingMatters.setMinutes(definedTimeBeforeNothingMatters.getMinutes()-3);
    definedTimeBeforeNothingMatters.setSeconds(59,99);
    nowDate.setSeconds(59,99);
  }
  if(dwmyChoose==6)
  {
    definedTimeBeforeNothingMatters.setMinutes(definedTimeBeforeNothingMatters.getMinutes()-10);
    definedTimeBeforeNothingMatters.setSeconds(59,99);
    nowDate.setSeconds(59,99);
  }
  dTime = (nowDate.getTime()-definedTimeBeforeNothingMatters.getTime())/counterSizeDependingOnDates;
}

function zeros(dimensions)
{
  var array = new Array();
  for (var i=0;i < dimensions[0];++i)
  {
    array.push(dimensions.length == 1 ? 0 : zeros(dimensions.slice(1)));
  }
  return array;
}

function FindTimeSlot(time)
{
  var timeSlot;
  for(var l=0;l<counterSizeDependingOnDates;l++)
  {
    if(dwmyChoose==3)
    {
      if (time > monthsTimes[l] && time <= monthsTimes[l+1]) timeSlot = l;
    }
    else
    {
      if (time.getTime() > (definedTimeBeforeNothingMatters.getTime()+l*dTime) && time.getTime() <= (definedTimeBeforeNothingMatters.getTime()+(l+1)*dTime)) timeSlot = l;
    }
  }
  return timeSlot;
}

function FetchFullData()
{
  for (var fetchedSensorIndex=0;fetchedSensorIndex<4;fetchedSensorIndex++)
  {
    try{
      var sumOfDataFromOneSensorForSpecificTime = zeros([counterSizeDependingOnDates,4]);
      var countOfDataInstancesFromOneSensor = new Array(counterSizeDependingOnDates).fill(0);
      for(var fetchedReadingIndex=0; fetchedReadingIndex<sensorReadingsCount[fetchedSensorIndex];fetchedReadingIndex++)
      {
        var distinctDataForOneSensor = [4];
        var fetchFromData = firebase.database().ref().child(sensorNames[fetchedSensorIndex]).child(sensorReadings[fetchedSensorIndex][fetchedReadingIndex]).child("DataList");
        fetchFromData.on('value', function(snap){
          for (var k=0;k<4;k++)
          {
              if(!isNaN(snap.val()[k])) distinctDataForOneSensor[k] = snap.val()[k];
              else distinctDataForOneSensor[k] = 0;
          }
        });

        var fetchFromTimestamp = firebase.database().ref().child(sensorNames[fetchedSensorIndex]).child(sensorReadings[fetchedSensorIndex][fetchedReadingIndex]).child("Timestamp");
        fetchFromTimestamp.on('value', function(snap){
          var time = new Date(snap.val());
          if(time >= definedTimeBeforeNothingMatters)
          {
            var timeSlot = FindTimeSlot(time);
            for (var k=0;k<4;k++) sumOfDataFromOneSensorForSpecificTime[timeSlot][k] += distinctDataForOneSensor[k];
            countOfDataInstancesFromOneSensor[timeSlot]++;
          }
        });
      }
      var averagesForOneSensor = zeros([counterSizeDependingOnDates,4]);
      for (var allOfTheTimeSlots=0; allOfTheTimeSlots<counterSizeDependingOnDates;allOfTheTimeSlots++)
      {
        for (var k=0;k<4;k++) averagesForOneSensor[allOfTheTimeSlots][k] = sumOfDataFromOneSensorForSpecificTime[allOfTheTimeSlots][k]/countOfDataInstancesFromOneSensor[allOfTheTimeSlots];
      }
      fullData[fetchedSensorIndex] = averagesForOneSensor;
    }catch (exception){
      console.log(exception);
    }
  }
}

function FixOneCharString(str)
{
  if(str < 10) return '0'+str;
  else return str;
}

function PrepareTimeString(i)
{
  var tempTime = new Date();
  tempTime.setTime(definedTimeBeforeNothingMatters.getTime()+dTime*i);

  if(dwmyChoose == 0)
    tempData = FixOneCharString(tempTime.getHours())+':00';
  else if(dwmyChoose == 1 || dwmyChoose == 2)
    tempData = FixOneCharString(tempTime.getDate()+1)
                +'.'+FixOneCharString(tempTime.getMonth()+1)
                +'.'+(tempTime.getYear()+1900);
  else if(dwmyChoose == 3)
    tempData = FixOneCharString(monthsTimes[i].getMonth()+2)
                +'.'+(tempTime.getYear()+1900);
  else if(dwmyChoose == 4)
    tempData = FixOneCharString(tempTime.getHours())
                +':'+FixOneCharString(tempTime.getMinutes())
                +':'+FixOneCharString(tempTime.getSeconds());
  else
    tempData = FixOneCharString(tempTime.getHours())
                +':'+FixOneCharString(tempTime.getMinutes()+1);

  return tempData;
}
</script>
