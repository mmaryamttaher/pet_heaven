
import './Dashboard.css'
import React, { PureComponent } from 'react';
import {
  BarChart, Bar, LineChart, Line, PieChart, Pie, Cell,
  XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts';

function Dashboard() {
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
  const data2= [
    {
      name: 'bondoq',
      type: 'cat',
      age: '2m',
      gender: 'male',
      owner:"fatma"
    },
    {
      name: 'bondoq',
      type: 'cat',
      age: '2m',
      gender: 'male',
      owner:"fatma"
    },
    {
      name: 'bondoq',
      type: 'cat',
      age: '2m',
      gender: 'male',
      owner:"fatma"
    },
    {
      name: 'bondoq',
      type: 'cat',
      age: '2m',
      gender: 'male',
      owner:"fatma"
    },
   
  ];

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

  const resdata = [
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

  const datam = [
    { month: 'JAN', revenue: 5200 },
    { month: 'FEB', revenue: 6100 },
    { month: 'MAR', revenue: 7000 },
    { month: 'APR', revenue: 4800 },
    { month: 'MAY', revenue: 5900 },
    { month: 'JUN', revenue: 6200 },
  ];

  const animalData = [
    { name: 'Dogs', value: 40 },
    { name: 'Cats', value: 30 },
    { name: 'Birds', value: 10 },
    { name: 'Turtles', value: 5 },
    { name: 'Another', value: 2 },
  ];

  const chardata = [
    { date: '10-6', bookings: 5 },
    { date: '11-6', bookings: 8 },
    { date: '12-6', bookings: 4 },
    { date: '13-6', bookings: 9 },
    { date: '14-6', bookings: 6 },
  ];

  const COLORS = ['#fc6832', '#fcdbbb', '#fcee9d', '#ff2929', '#13077a'];


  
  return (
    <div>
      <h1>Dashboard</h1>
      <br />
      <h1>Our Products In</h1>
    <div className='chart1'>
      
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
      </div> <br />

    <div className='inline'>
    <div className='chart2'>
    <table className="pet-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Owner</th>
          </tr>
        </thead>
        <tbody>
          {data2.map((pet, index) => (
            <tr key={index}>
              <td>{pet.name}</td>
              <td>{pet.type}</td>
              <td>{pet.age}</td>
              <td>{pet.gender}</td>
              <td>{pet.owner}</td>
            </tr>
          ))}
        </tbody>
      </table>
      </div> <br />
    <div className='chart3'>
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
      </div> <br />
    </div> <br />

    <div className='chart4'>
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
          {resdata.map((reservation, index) => (
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
      </div> <br />
    <div className='inline'>
    <div className='chart5'>
       <ResponsiveContainer width="100%" height="100%">
          <BarChart data={datam}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="month" />
            <YAxis />
            <Tooltip />
            <Legend />
            <Bar dataKey="revenue" fill="#10b981" />
          </BarChart>
        </ResponsiveContainer>
      </div> <br />
    <div className='chart6'>
    <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={animalData}
                dataKey="value"
                nameKey="name"
                cx="50%"
                cy="50%"
                outerRadius={100}
                label
              >
                {animalData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </PieChart>
          </ResponsiveContainer>
      </div> <br />
    <div className='chart7'>
    <ResponsiveContainer width="100%" height="100%">
            <LineChart data={chardata}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="bookings" stroke="#6366f1" />
            </LineChart>
          </ResponsiveContainer>
      </div> <br />
    </div>
    </div>
  )
}

export default Dashboard
