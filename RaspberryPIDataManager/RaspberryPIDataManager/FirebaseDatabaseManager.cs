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

namespace RaspberryPIDataManager
{
    public class FirebaseDatabaseManager
    {
        private IFirebaseClient client;

        /// <summary>
        /// Default constructor with connection initialization
        /// </summary>
        public FirebaseDatabaseManager()
        {
            InitDatabaseClient();
        }

        public IFirebaseClient Client { get => client; set => client = value; }

        /// <summary>
        /// Initialization of Database connection
        /// </summary>
        private void InitDatabaseClient()
        {
            IFirebaseConfig config = new FirebaseConfig
            {
                AuthSecret = "SMH1AVPXTihCeTj4bnVWYJK3oc4vnG2gXUI32vhh",
                BasePath = "https://eit-siu-iot.firebaseio.com"
            };

            Client = new FirebaseClient(config);
        }

        /// <summary>
        /// Push data to Firebase
        /// </summary>
        /// <param name="temp"></param>
        /// <param name="pressure"></param>
        /// <param name="sensorId"></param>
        /// <returns></returns>
        public async Task pushAsync(double temp, double pressure, string sensorId)
        {
            try
            {
                var data = new Sensor(temp, pressure, sensorId);
                string queryTempValues = String.Format("Sensor_{0}/Data", sensorId);
                PushResponse response = await Client.PushAsync(queryTempValues, data.SensorData);
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

        //
        // Declare that your background task's Run method makes asynchronous calls by
        // using the async keyword.
        //
        /// <summary>
        /// Asynchronous safety call for .net core applications
        /// </summary>
        /// <param name="taskInstance"></param>
        /// <param name="temp"></param>
        /// <param name="pressure"></param>
        /// <param name="sensorId"></param>
        public static async void PushDataToFireabase(IBackgroundTaskInstance taskInstance, double temp, double pressure, string sensorId)
        {
            FirebaseDatabaseManager firebaseDatabaseManager = new FirebaseDatabaseManager();
            //
            // Create the deferral by requesting it from the task instance.
            //
            BackgroundTaskDeferral deferral = taskInstance.GetDeferral();

            //
            // Call asynchronous method(s) using the await keyword.
            //
            await firebaseDatabaseManager.pushAsync(temp, pressure, sensorId);
            //
            // Once the asynchronous method(s) are done, close the deferral.
            //
            deferral.Complete();
        }
    }
}
