import React from 'react'
import "./Navbar.css"
/*import UilReact from '@iconscout/react-unicons/icons/uil-react'
import FontAwesomeIcon from "@fortawesome/fontawesome-svg-core"*/
function Navbar() {
  return (
    <div className='navbar'>
      {}
      <div className='brand-name'>
        <h1>PET HEAVEN</h1>
      </div>
        
        <div className='menu'>
          <ul>
          <div className='menuitems'>
           <li><a href ="/">
              Dashboard
            </a></li> 
          </div>
          <div className='menuitems'>
           <li> <a href="./Products">
              Clients
            </a></li>
          </div>
          <div className='menuitems'>
          <li> <a href="./Employees">
              Pets
            </a> </li>
          </div>
          <div className='menuitems'>
          <li> <a href="./Customers">
              Rooms
            </a></li>
          </div>
          <div className='menuitems'>
          <li> <a href="./Orders">
              Reservations
            </a></li>
          </div>
          <div className='menuitems'>
          <li> <a href="./Finance">
              Report
            </a></li>
          </div>
          <div className='menuitems'>
          <li> <a href="./LogOut">
              Log Out
            </a></li>
          </div>
          </ul>
        </div>
    </div>
  )
}

export default Navbar
