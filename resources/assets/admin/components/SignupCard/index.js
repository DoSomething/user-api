import React from 'react';
import gql from 'graphql-tag';
import { Link } from 'react-router-dom';

import PostTile from '../PostTile';

import './signup-card.scss';

const POST_GALLERY_SIZE = 4;

export const SignupCardFragment = gql`
  fragment SignupCardFragment on Signup {
    id
    quantity
    whyParticipated

    campaign {
      id
      internalTitle
      startDate
    }

    posts {
      id
      type
      url(w: 200, h: 200)
    }
  }
`;

const SignupCard = ({ signup }) => {
  const campaign = signup.campaign || {};

  const extraPostCount = signup.posts.length - POST_GALLERY_SIZE;
  const posts = signup.posts.slice(0, POST_GALLERY_SIZE).map(post => (
    <li key={post.id}>
      <PostTile post={post} />
    </li>
  ));

  return (
    <article className="container__row signup-card">
      <Link to={`/signups/${signup.id}`}>
        <div className="container__block -half">
          <div className="container__row">
            <h2 className="heading">
              {campaign.internalTitle || 'Unknown Campaign'}
            </h2>
            <h4 className="heading">Campaign ID: {campaign.id || 'N/A'}</h4>
            <h4 className="heading">
              Campaign Start Date: {campaign.startDate || '???'}
            </h4>
          </div>
          <div className="container__row">
            <h4 className="heading">Why Statement:</h4>
            <p>{signup.whyParticipated}</p>
          </div>
        </div>
        <div className="container__block -half">
          {signup.quantity ? (
            <div className="container__row figure -left -center">
              <div className="figure__media">
                <div className="quantity">{signup.quantity}</div>
              </div>
              <div className="figure__body">
                <h4 className="reportback-noun-verb">things done</h4>
              </div>
            </div>
          ) : null}
          {posts.length ? (
            <div className="container__row">
              <h4>Items</h4>
              <ul className="gallery -quartet">
                {posts}
                {extraPostCount > 0 ? (
                  <li className="figure__media">
                    <div className="quantity">+{extraPostCount}</div>
                  </li>
                ) : null}
              </ul>
            </div>
          ) : null}
        </div>
      </Link>
    </article>
  );
};

export default SignupCard;
