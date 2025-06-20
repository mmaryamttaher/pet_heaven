import React, { PureComponent } from 'react';
import {
  BarChart, Bar, LineChart, Line, PieChart, Pie, Cell,
  XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts';
import './Finance.css'

/*function report() {
  const data = [
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
  const COLORS = ['#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#FF6699'];

  const resdata = [
    { date: '10-6', bookings: 5 },
    { date: '11-6', bookings: 8 },
    { date: '12-6', bookings: 4 },
    { date: '13-6', bookings: 9 },
    { date: '14-6', bookings: 6 },
  ];
 
  
  return (
    <div>
      <h1>Report</h1>
      <br />
      <h1>Revenue</h1>
    <div className='chart1'>
      
    <ResponsiveContainer width="100%" height="100%">
    <BarChart width={600} height={300} data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey="month" />
        <YAxis />
        <Tooltip />
        <Legend />
        <Bar dataKey="revenue" fill="#10b981" />
      </BarChart>
      </ResponsiveContainer>
      </div> <br />

    <div className='inline'>
    <div className='chart2'>
    <ResponsiveContainer width="100%" height="100%">
    <PieChart width={400} height={300}>
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
            <Cell key={index} fill={COLORS[index % COLORS.length]} />
          ))}
        </Pie>
        <Tooltip />
        <Legend />
      </PieChart>
      </ResponsiveContainer>
      </div> <br />
    <div className='chart3'>
    <ResponsiveContainer width="100%" height="100%">
    <LineChart width={600} height={300} data={resdata}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey="data" />
        <YAxis />
        <Tooltip />
        <Legend />
        <Line type="monotone" dataKey="bookings" stroke="#6366f1" />
      </LineChart>
    </ResponsiveContainer>
      </div> <br />
    </div> <br />

    
    

    </div>
    
  )
}

export default report*/



function Report() {
  const data = [
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

  const resdata = [
    { date: '10-6', bookings: 5 },
    { date: '11-6', bookings: 8 },
    { date: '12-6', bookings: 4 },
    { date: '13-6', bookings: 9 },
    { date: '14-6', bookings: 6 },
  ];

  const COLORS = ['#fc6832', '#fcdbbb', '#fcee9d', '#ff2929', '#13077a'];

  return (
    <div>
      <h1>Report</h1>
      <br />
      <h2>Revenue</h2>
      <div className='chart1'>
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="month" />
            <YAxis />
            <Tooltip />
            <Legend />
            <Bar dataKey="revenue" fill="#10b981" />
          </BarChart>
        </ResponsiveContainer>
      </div>

      <br />
      <div className='inline'>
        <div className='chart2'>
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
        </div>

        <div className='chart3'>
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={resdata}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="bookings" stroke="#6366f1" />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
}

export default Report;