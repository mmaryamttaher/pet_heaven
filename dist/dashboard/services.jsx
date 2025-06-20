import React, { PureComponent } from 'react';
import './services.css'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
function services() {
    const data = [
        {
          srvice: 'Page A',
          price: 4000,
          duration: 2400,
          note: 2400,
        },
        
      ];
      return (
        <div className="'customer'">
         <div><h1>Services</h1></div>
         <div className='chart'>
         <table className="customer-table">
             <thead>
               <tr>
                 <th>Service</th>
                 <th>Price</th>
                 <th>Duration</th>
                 <th>Note</th>
               </tr>
             </thead>
             <tbody>
               {data.map((Services, index) => (
                 <tr key={index}>
                   <td>{Services.srvice}</td>
                   <td>{Services.price}</td>
                   <td>{Services.duration}</td>
                   <td>{Services.note}</td>
                 </tr>
               ))}
             </tbody>
           </table>
        </div>
        </div>
       )
     }

export default services