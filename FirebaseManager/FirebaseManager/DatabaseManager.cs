using FireSharp;
using FireSharp.Config;
using FireSharp.Interfaces;
using FireSharp.Response;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace FirebaseManager
{
    public class DatabaseManager<T>
    {
        private IFirebaseClient firebaseClient;
        public IFirebaseClient FirebaseClient { get => firebaseClient; set => firebaseClient = value; }



        private void InitDatabaseClient()
        {
            IFirebaseConfig config = new FirebaseConfig
            {
                AuthSecret = "SMH1AVPXTihCeTj4bnVWYJK3oc4vnG2gXUI32vhh",
                BasePath = "https://eit-siu-iot.firebaseio.com"
            };

            FirebaseClient = new FirebaseClient(config);
        }
        public DatabaseManager()
        {
            InitDatabaseClient();
        }

        public async Task PushAsync(string sensorId, string time, params T[] inputToSend)
        {
            try
            {
                var data = new DataObject<T>(sensorId,time, inputToSend);


                string queryTempValues = String.Format("Sensor_{0}", sensorId);

                PushResponse response = await FirebaseClient.PushAsync(queryTempValues, data.DataStructureObject);
                if (response.StatusCode == System.Net.HttpStatusCode.OK)
                {
                    Debug.WriteLine("Data push - success");
                }
                else
                {
                    Debug.WriteLine("Database error: " + response.StatusCode.ToString());
                }
            }
            catch (Exception)
            {
                throw;
            }
        }
    }
}
