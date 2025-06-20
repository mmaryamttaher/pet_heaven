import React from 'react'
import './Logout.css'
function LogOut() {
  return (
    <div className='container'>
    <form>
        <label htmlFor="email">Enter Your E-mail</label> <br />
        <input type="text" id='email' placeholder='ENTER YOUR E_MAIL'/> <br />
        <label htmlFor="pass">Enter Your Password</label> <br />
        <input type="password" id='pass' placeholder='ENTER YOUR PASSWORD'/> <br /> <br />
        <button>Log IN</button>
    </form>
    </div>
  )
}

export default LogOut
