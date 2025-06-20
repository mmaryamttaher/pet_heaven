import React from 'react'
import './Employees.css'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
function Employees() {
  const data = [
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
  return (
    <div className='employees'>
      <div>
        <h1>Pets</h1>
      </div>
      <div className='box'>
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
          {data.map((pet, index) => (
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
      </div>
    </div>
  )
}

export default Employees

