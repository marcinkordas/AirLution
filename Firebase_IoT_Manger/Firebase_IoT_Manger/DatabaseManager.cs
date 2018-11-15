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
using Windows.ApplicationModel.Background;

namespace Firebase_IoT_Manger
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

        public async Task pushAsync(string sensorId, params T[] inputToSend)
        {
            try
            {
                var data = new DataObject<T>(sensorId, inputToSend);


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
        public static async void PushDataToFireabase(IBackgroundTaskInstance taskInstance, string sensorId, params T[] inputToSend)
        {
            try
            {
                DatabaseManager<T> firebaseDatabaseManager = new DatabaseManager<T>();
                //
                // Create the deferral by requesting it from the task instance.
                //
                BackgroundTaskDeferral deferral = taskInstance.GetDeferral();

                //
                // Call asynchronous method(s) using the await keyword.
                //
                await firebaseDatabaseManager.pushAsync(sensorId, inputToSend);
                //
                // Once the asynchronous method(s) are done, close the deferral.
                //
                deferral.Complete();
            }
            catch (Exception ex)
            {
                throw ex;
            }

        }
    }
}
