import React, { PureComponent } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import './Orders.css'
function Orders() {
    const data = [
        {
          pet: 'bondoq',
          owner: 'fatma',
          room: '154',
          checkin: '20-4',
          checkout: '25-4',
          status: 'finished',
        },
        {
          pet: 'bondoq',
          owner: 'fatma',
          room: '154',
          checkin: '20-4',
          checkout: '25-4',
          status: 'valid',
        },
        {
          pet: 'bondoq',
          owner: 'fatma',
          room: '154',
          checkin: '20-4',
          checkout: '25-4',
          status: 'finished',
        },
       
      ];
  return (
    <div className='order'>
      <div><h1>Reservations</h1></div>
      <div className='chart'>
      <table className="customer-table">
        <thead>
          <tr>
            <th>Pet</th>
            <th>Owner</th>
            <th>Room</th>
            <th>Check in</th>
            <th>Check out</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {data.map((reservation, index) => (
            <tr key={index}>
              <td>{reservation.pet}</td>
              <td>{reservation.owner}</td>
              <td>{reservation.room}</td>
              <td>{reservation.checkin}</td>
              <td>{reservation.checkout}</td>
              <td>{reservation.status}</td>
            </tr>
          ))}
        </tbody>
      </table>
      </div>
    </div>
  )
}

export default Orders
