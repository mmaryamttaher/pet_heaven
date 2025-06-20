import React, { PureComponent } from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import './Products.css'

function Products() {
  const data = [
    { 
      name: 'Fatma',
      phone: '010258653' ,
      email:'dxasd@gmail.com' ,
      petsCount:'1',
      lastVisit: '20-5'
     } ,
     { 
       name: 'abdelrahman',
       phone: '0124424' ,
       email:'gff@gmail.com' ,
       petsCount:'2',
       lastVisit: '20-4'
      } ,
      { 
       name: 'sara',
       phone: '0158828' ,
       email:'fwef@gmail.com' ,
       petsCount:'1',
       lastVisit: '4-4'
      }
      
  ];
  return (
    <div className='products'>
      <div><h1>Clients</h1></div>
      <div className='chart'>
      <table className="customer-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>E-Mail</th>
            <th>Pets Count</th>
            <th>Last Visit</th>
          </tr>
        </thead>
        <tbody>
          {data.map((customer, index) => (
            <tr key={index}>
              <td>{customer.name}</td>
              <td>{customer.phone}</td>
              <td>{customer.email}</td>
              <td>{customer.petsCount}</td>
              <td>{customer.lastVisit}</td>
            </tr>
          ))}
        </tbody>
      </table>
      </div>
    </div>
  )
}

export default Products
