using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RaspberryPIDataManager
{
    class Sensor
    {
        public class SensorDataClass
        {
            private double temperature;
            private double pressure;

            public SensorDataClass(double temperature, double pressure)
            {
                Temperature = temperature;
                Pressure = pressure;
            }

            public double Temperature { get => temperature; set => temperature = value; }
            public double Pressure { get => pressure; set => pressure = value; }
        }

        private SensorDataClass sensorData;
        private string sensorId;

        public Sensor(double temperature, double pressure, string sensorId)
        {
            SensorData = new SensorDataClass(temperature, pressure);
            SensorId = sensorId;
        }


        public string SensorId { get => sensorId; set => sensorId = value; }
        public SensorDataClass SensorData { get => sensorData; set => sensorData = value; }
    }
}
