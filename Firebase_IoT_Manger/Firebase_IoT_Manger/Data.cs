using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Firebase_IoT_Manger
{
    public class DataObject<T>
    {
        public class DataStructure
        {
            private List<T> dataList;
            public List<T> DataList { get => dataList; set => dataList = value; }

            public DataStructure(params T[] input)
            {
                DataList = new List<T>();

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

        public DataObject(string dataToSendId, params T[] input)
        {
            DataId = dataToSendId;
            dataStructureObject = new DataStructure(input);
        }
    }
}
