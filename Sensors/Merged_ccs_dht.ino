/*
  ccs811basic.ino - Demo sketch printing results of the CCS811 digital gas sensor for monitoring indoor air quality from ams.
  Created by Maarten Pennings 2017 Dec 11
*/


#include <Wire.h>    // I2C library
#include "ccs811.h"  // CCS811 library
#include "DHTesp.h"
#include "assert.h"
#ifdef ESP32
#pragma message(THIS EXAMPLE IS FOR ESP8266 ONLY!)
#error Select ESP8266 board.
#endif
// Wiring for ESP8266 NodeMCU boards: VDD to 3V3, GND to GND, SDA to D2, SCL to D1, nWAKE to D3 (or GND)
CCS811 ccs811(D3,0x5B); // nWAKE on D3
DHTesp dht;


void setup() {
  // Enable serial
  Serial.begin(115200);
  Serial.println("");
  Serial.println("Starting CCS811 basic demo");

  // Enable I2C
  Wire.begin(); 
  dht.setup(2, DHTesp::DHT22);
  // Enable CCS811
  ccs811.set_i2cdelay(50); // Needed for ESP8266 because it doesn't handle I2C clock stretch correctly
  bool ok= ccs811.begin();
  if( !ok ) Serial.println("init: CCS811 begin FAILED");

  // Print CCS811 versions
  Serial.print("init: hardware    version: "); Serial.println(ccs811.hardware_version(),HEX);
  Serial.print("init: bootloader  version: "); Serial.println(ccs811.bootloader_version(),HEX);
  Serial.print("init: application version: "); Serial.println(ccs811.application_version(),HEX);
  
  // Start measuring
  ok= ccs811.start(CCS811_MODE_1SEC);
  if( !ok ) Serial.println("init: CCS811 start FAILED");
}


void loop() {
  // Read
  uint16_t eco2, etvoc, errstat, raw;
  ccs811.read(&eco2,&etvoc,&errstat,&raw); 
  char temp[100];
  char wilg[100];
  char czast[100];
  char zadym[100];
  delay(dht.getMinimumSamplingPeriod());
  float humidity = dht.getHumidity();
  float temperature = dht.getTemperature();
  assert(ccs811.set_envdata((uint16)temperature, (uint16) humidity));
 
  Serial.print(dht.getStatusString());
  Serial.print("\t");
  Serial.print(humidity, 1);
  Serial.print("\t\t");
  Serial.print(temperature, 1);
  Serial.print("\t\t");
  Serial.print(dht.toFahrenheit(temperature), 1);
  Serial.print("\t\t");
  Serial.print(dht.computeHeatIndex(temperature, humidity, false), 1);
  Serial.print("\t\t");
  Serial.println(dht.computeHeatIndex(dht.toFahrenheit(temperature), humidity, true), 1);
  gcvt(temperature, 5, temp);
  gcvt(humidity, 5, wilg);
  delay(500); 
  
  // Print measurement results based on status
  if( errstat==CCS811_ERRSTAT_OK ) { 
    Serial.print("CCS811: ");
    Serial.print("eco2=");  Serial.print(eco2);     Serial.print(" ppm  ");
    Serial.print("etvoc="); Serial.print(etvoc);    Serial.print(" ppb  ");
    //Serial.print("raw6=");  Serial.print(raw/1024); Serial.print(" uA  "); 
    //Serial.print("raw10="); Serial.print(raw%1024); Serial.print(" ADC  ");
    //Serial.print("R="); Serial.print((1650*1000L/1023)*(raw%1024)/(raw/1024)); Serial.print(" ohm");
    Serial.println();
  } else if( errstat==CCS811_ERRSTAT_OK_NODATA ) {
    Serial.println("CCS811: waiting for (new) data");
    //Serial.print("CCS811: errstat="); 
    //Serial.print(errstat,HEX); 
  } else if( errstat & CCS811_ERRSTAT_I2CFAIL ) { 
    Serial.println("CCS811: I2C error");
  } else {
    Serial.print("CCS811: errstat="); Serial.print(errstat,HEX); 
    Serial.print("="); Serial.println( ccs811.errstat_str(errstat) ); 
  }
  gcvt(eco2, 5, czast);
  gcvt(etvoc, 5, zadym);

  strcat(temp," ");
  strcat(temp, wilg);
  strcat(temp, " ");
  strcat(temp, czast);
  strcat(temp, " ");
  strcat(temp, zadym);
  
  // Wait
  Serial.println();Serial.print("Dane: ");
  Serial.print( "%s", temp);

  delay(2000); 
}