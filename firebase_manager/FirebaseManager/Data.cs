using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace FirebaseManager
{
    public class DataObject<T>
    {
        public class DataStructure
        {
            private List<T> dataList;
            public List<T> DataList { get => dataList; set => dataList = value; }
            private string timestamp;

            public string Timestamp { get => timestamp; set => timestamp = value; }

            public DataStructure(string time, params T[] input)
            {
                DataList = new List<T>();
                Timestamp = time;
                foreach (T _data in input)
                {
                    DataList.Add(_data);
                }
            }
        }


        private DataStructure dataStructureObject;
        private string dataId;
        public string DataId { get => dataId; set => dataId = value; }


        public DataStructure DataStructureObject { get => dataStructureObject; set => dataStructureObject = value; }

        public DataObject(string dataToSendId, string time, params T[] input)
        {
            DataId = dataToSendId;
            dataStructureObject = new DataStructure(time, input);
        }
    }
}
