import React from 'react'
import './Layout.css'
function Layout() {
  return (
    <div>
    <div className='brand-name'>
        <h1>GLOWING FLAME</h1>
      </div>
    <div className='row'>
        <div className='col'>
            <ul>
                <li>
                    <link to="/">Dashboard</link>
                </li>
                <li>
                    <link to="/Products">Products</link>
                </li>
                <li>
                    <link to="/Customers">Customers</link>
                </li>
                <li>
                    <link to="/Employees">Employees</link>
                </li>
                <li>
                    <link to="/LogOut">Log Out</link>
                </li>
            </ul>
        </div>
      
    </div>
    </div>
  )
}

export default Layout
