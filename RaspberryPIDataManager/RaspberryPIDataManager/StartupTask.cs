using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net.Http;
using Windows.ApplicationModel.Background;
using System.Threading.Tasks;

// The Background Application template is documented at http://go.microsoft.com/fwlink/?LinkID=533884&clcid=0x409

namespace RaspberryPIDataManager
{
    public sealed class StartupTask : IBackgroundTask
    {
        //
        // Declare that your background task's Run method makes asynchronous calls by
        // using the async keyword.
        //
        public async void PushDataToFireabase(IBackgroundTaskInstance taskInstance, double temp, double pressure, string sensorId)
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

        public void Run(IBackgroundTaskInstance taskInstance)
        {
            // 
            // TODO: Insert code to perform background work
            //

            PushDataToFireabase(taskInstance, 10, 11, "13");
            // If you start any asynchronous methods here, prevent the task
            // from closing prematurely by using BackgroundTaskDeferral as
            // described in http://aka.ms/backgroundtaskdeferral
            //
        }
    }
}
