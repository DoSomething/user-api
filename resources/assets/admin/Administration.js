import React from 'react';
import { BrowserRouter, Route, Switch, Redirect } from 'react-router-dom';
import { ApolloProvider } from '@apollo/react-hooks';

import { env } from './helpers';
import graphql from './graphql';
import ShowClub from './pages/ShowClub';
import ShowPost from './pages/ShowPost';
import ShowUser from './pages/ShowUser';
import PostIndex from './pages/PostIndex';
import UserIndex from './pages/UserIndex';
import ClubIndex from './pages/ClubIndex';
import ShowGroup from './pages/ShowGroup';
import ShowAction from './pages/ShowAction';
import ShowSignup from './pages/ShowSignup';
import ShowSchool from './pages/ShowSchool';
import SignupIndex from './pages/SignupIndex';
import ShowCampaign from './pages/ShowCampaign';
import ShowGroupType from './pages/ShowGroupType';
import CampaignIndex from './pages/CampaignIndex';
import GroupTypeIndex from './pages/GroupTypeIndex';
import ActionStatIndex from './pages/ActionStatIndex';

const Application = () => {
  const endpoint = env('GRAPHQL_URL');

  return (
    <ApolloProvider client={graphql(endpoint)}>
      <BrowserRouter>
        <Switch>
          <Route path="/admin/action-stats" exact>
            <ActionStatIndex />
          </Route>

          <Route path="/admin/actions/:id">
            <ShowAction />
          </Route>

          <Route path="/admin/campaigns" exact>
            <CampaignIndex isOpen={true} />
          </Route>

          <Route path="/admin/campaigns/closed" exact>
            <CampaignIndex isOpen={false} />
          </Route>

          <Route path="/admin/campaigns/:id" exact>
            <ShowCampaign />
          </Route>

          <Redirect
            from="/admin/campaigns/:id/inbox"
            to="/admin/campaigns/:id/pending"
          />

          <Route path="/admin/campaigns/:id/:status">
            <ShowCampaign />
          </Route>

          <Route path="/admin/clubs" exact>
            <ClubIndex />
          </Route>

          <Route path="/admin/clubs/:id">
            <ShowClub />
          </Route>

          <Route path="/admin/groups" exact>
            <GroupTypeIndex />
          </Route>

          <Route path="/admin/group-types" exact>
            <GroupTypeIndex />
          </Route>

          <Route path="/admin/group-types/:id">
            <ShowGroupType />
          </Route>

          <Route path="/admin/groups/:id/posts" exact>
            <ShowGroup selectedTab="posts" />
          </Route>

          <Route path="/admin/groups/:id">
            <ShowGroup />
          </Route>

          <Route path="/admin/users" exact>
            <UserIndex />
          </Route>

          <Route path="/admin/users/:id/posts" exact>
            <ShowUser selectedTab="posts" />
          </Route>

          <Route path="/admin/users/:id/referrals" exact>
            <ShowUser selectedTab="referrals" />
          </Route>

          <Route path="/admin/activity/:id">
            <ShowUser />
          </Route>

          <Route path="/admin/posts" exact>
            <PostIndex />
          </Route>

          <Route path="/admin/posts/:id">
            <ShowPost />
          </Route>

          <Route path="/admin/signups" exact>
            <SignupIndex />
          </Route>

          <Route path="/admin/signups/:id">
            <ShowSignup />
          </Route>

          <Route path="/admin/schools/:id">
            <ShowSchool />
          </Route>
        </Switch>
      </BrowserRouter>
    </ApolloProvider>
  );
};

export default Application;
