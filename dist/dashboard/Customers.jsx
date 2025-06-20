import React, { PureComponent } from 'react';
import { PieChart, Pie, Legend, Tooltip, ResponsiveContainer } from 'recharts';
import './Coustomers.css'
function Coustomers() {
  const data01 = [
    { 
     number: '1521',
     status: 'available' ,
     type:'single' ,
     price:'1000',
     CurrentPet: 'bondoq'
    } ,
    { 
      number: '154',
     status: 'unavailable' ,
     type:'single' ,
     price:'1000',
     CurrentPet: '-'
    } ,
     { 
      number: '458',
      status: 'available' ,
      type:'double' ,
      price:'1000',
      CurrentPet: 'bondoq'
     } ,
    
  ];
  return (
   <div className="'customer'">
    <div><h1>Rooms</h1></div>
    <div className='chart'>
    <table className="customer-table">
        <thead>
          <tr>
            <th>Number</th>
            <th>Status</th>
            <th>Type</th>
            <th>Price</th>
            <th>Current Pet</th>
          </tr>
        </thead>
        <tbody>
          {data01.map((room, index) => (
            <tr key={index}>
              <td>{room.number}</td>
              <td>{room.status}</td>
              <td>{room.type}</td>
              <td>{room.price}</td>
              <td>{room.CurrentPet}</td>
            </tr>
          ))}
        </tbody>
      </table>
   </div>
   </div>
  )
}

export default Coustomers
