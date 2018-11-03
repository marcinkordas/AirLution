using FireSharp;
using FireSharp.Config;
using FireSharp.Interfaces;
using FireSharp.Response;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RaspberryPIDataManager
{
    class FirebaseDatabaseManager
    {
        private IFirebaseClient client;

        public FirebaseDatabaseManager()
        {
            InitDatabaseClient();
        }

        public IFirebaseClient Client { get => client; set => client = value; }

        private void InitDatabaseClient()
        {
            IFirebaseConfig config = new FirebaseConfig
            {
                AuthSecret = "SMH1AVPXTihCeTj4bnVWYJK3oc4vnG2gXUI32vhh",
                BasePath = "https://eit-siu-iot.firebaseio.com"
            };

            Client = new FirebaseClient(config);
        }

        public async Task pushAsync(double temp, double pressure, string sensorId)
        {
            try
            {
                var data = new Sensor(temp, pressure, sensorId);
                string queryTempValues = String.Format("Sensor_{0}/Data", sensorId);
                PushResponse response = await Client.PushAsync(queryTempValues, data.SensorData);
                if (response.StatusCode == System.Net.HttpStatusCode.OK)
                {
                    //Console.WriteLine("Data pushed"); //For Debug
                }
                else
                {
                    //Console.WriteLine("Database error: " + response.StatusCode.ToString()); //For Debug
                }
            }
            catch (Exception)
            {
                throw;
            }
        }
    }
}
