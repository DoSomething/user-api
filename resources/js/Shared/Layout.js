import React, { useEffect } from 'react';
import { InertiaLink } from '@inertiajs/inertia-react';

import DoSomethingLogo from '../Shared/DoSomethingLogo/DoSomethingLogo';

const Layout = ({ children, title }) => {
  useEffect(() => {
    document.title = title;
  }, [title]);

  return (
    <div className="page h-screen">
      <h1 className="bg-blurple-500 col-span-1 flex items-center px-4 py-2">
        <DoSomethingLogo className="w-16" />
        {/* <span className="font-bold ml-4 text-2xl uppercase">Admin</span> */}
      </h1>

      <header className="col-span-1 bg-gray-200 flex justify-end">
        <p className="p-4 text-gray-600">Logout</p>
      </header>

      <nav className="bg-blurple-700 col-span-1 p-4 text-white">
        <ul className="pb-4">
          <li className="">
            <InertiaLink href="" className="block py-2">
              FAQ
            </InertiaLink>
          </li>
          <li className="">
            <InertiaLink href="" className="block py-2">
              Campaigns
            </InertiaLink>
          </li>
          <li className="">
            <InertiaLink href="" className="block py-2">
              Clubs
            </InertiaLink>
          </li>
          <li className="">
            <InertiaLink href="" className="block py-2">
              Redirects
            </InertiaLink>
          </li>
          <li className="">
            <InertiaLink href="" className="block py-2">
              Users
            </InertiaLink>
          </li>
        </ul>

        <ul className="border-t border-t-solid border-blurple-400 pt-4">
          <li className="">
            <InertiaLink href="" className="block py-2">
              Super Users
            </InertiaLink>
          </li>
          <li className="">
            <InertiaLink href="" className="block py-2">
              OAuth Clients
            </InertiaLink>
          </li>
        </ul>
      </nav>

      <main className="col-span-1">{children}</main>
    </div>
  );
};

export default Layout;
