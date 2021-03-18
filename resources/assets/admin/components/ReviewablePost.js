import React from 'react';
import { map } from 'lodash';
import gql from 'graphql-tag';
import usePortal from 'react-useportal';
import { Link } from 'react-router-dom';

import Quantity from './utilities/Quantity';
import PostTile from './PostTile';
import HelpLink from './utilities/HelpLink';
import TextBlock from './utilities/TextBlock';
import Modal from './utilities/Modal';
import PostCard from './utilities/PostCard';
import { formatDateTime, TAGS } from '../helpers';
import DeletePostButton from './DeletePostButton';
import TagButton, { TagButtonFragment } from './TagButton';
import QuantityForm, { QuantityFormFragment } from './QuantityForm';
import MetaInformation from './utilities/MetaInformation';
import {
  AcceptButton,
  RejectButton,
  ReviewButtonFragment,
} from './ReviewButton';
import UserInformation, {
  UserInformationFragment,
} from './utilities/UserInformation';

export const ReviewablePostFragment = gql`
  fragment ReviewablePost on Post {
    ...ReviewButton
    ...TagButton
    ...QuantityForm
    quantity
    hoursSpent
    text
    type
    url
    createdAt
    source
    details
    clubId
    groupId
    referrerUserId
    schoolId
    location(format: HUMAN_FORMAT)
    deleted

    actionDetails {
      id
      name
      noun
      verb
    }

    campaign {
      id
      internalTitle
    }

    signupId
    signup {
      id
      whyParticipated
      source
    }

    userId
    user {
      id
      ...UserInformation
    }
  }

  ${ReviewButtonFragment}
  ${TagButtonFragment}
  ${QuantityFormFragment}
  ${UserInformationFragment}
`;

const ReviewablePost = ({ post }) => {
  var { openPortal, closePortal, isOpen, Portal } = usePortal({
    bindTo: document.getElementById('modal-portal'),
  });

  // If we have a 'deleted' flag, it means that we've deleted this
  // post since first viewing it & cannot make any further changes.
  if (post.deleted) {
    return (
      <div className="border-solid border-gray-300 border-b-2 py-4 my-4">
        <p className="text-sm text-gray text-center italic">
          This post (#{post.id}) has been deleted.
        </p>
      </div>
    );
  }

  return (
    <div className="border-solid border-gray-300 border-b-2 py-4 my-4 clearfix">
      <div className="container__block -third">
        <PostCard post={post} />
        <ul className="gallery -duo">
          {[
            /* todo */
          ].map(siblingPost => (
            <li key={siblingPost.id}>
              <PostTile post={siblingPost} />
            </li>
          ))}
        </ul>
      </div>

      <div className="container__block -third">
        <UserInformation
          user={post.user}
          userId={post.userId}
          linkSignup={post.signupId}
        >
          {post.quantity ? (
            <Quantity
              quantity={post.quantity}
              noun={post.actionDetails.noun}
              verb={post.actionDetails.verb}
            />
          ) : null}

          {post.quantity ? (
            <div className="container -padded">
              <button className="button -tertiary" onClick={openPortal}>
                Edit Quantity
              </button>

              {isOpen ? (
                <Portal>
                  <Modal onClose={closePortal}>
                    <QuantityForm post={post} />
                  </Modal>
                </Portal>
              ) : null}
            </div>
          ) : null}

          <div className="container -padded">
            <TextBlock
              title={post.type === 'photo' ? 'Caption' : 'Text'}
              content={post.text}
            />
          </div>

          <div className="container">
            <TextBlock
              title="Why Statement"
              content={post.signup ? post.signup.whyParticipated : 'N/A'}
            />
          </div>
        </UserInformation>
      </div>

      <div className="container__block -third">
        <div className="container__row">
          <ul className="form-actions -inline">
            <li>
              <AcceptButton post={post} />
            </li>

            <li>
              <RejectButton post={post} />
            </li>
          </ul>
        </div>

        <div className="container__row">
          <ul className="form-actions -inline">
            <li>
              <DeletePostButton post={post} />
            </li>
          </ul>
        </div>

        {post.status !== 'PENDING' ? (
          <div className="container__row">
            <h4>
              Tags <HelpLink to="/faq#tags" title="Tag definitions" />
            </h4>

            {map(TAGS, (label, id) => (
              <TagButton key={id} tag={id} label={label} post={post} />
            ))}
          </div>
        ) : null}

        <div className="container__row">
          <MetaInformation
            title="Post Information"
            details={{
              ID: <Link to={`/admin/posts/${post.id}`}>{post.id}</Link>,
              Campaign: (
                <a href={`/admin/campaigns/${post.campaign.id}`}>
                  {post.campaign.internalTitle}
                </a>
              ),
              Action: (
                <a href={`/admin/actions/${post.actionDetails.id}`}>
                  {post.actionDetails.name}
                </a>
              ),
              Type: post.type,
              Source: post.source,
              Location: post.location || '-',
              Submitted: formatDateTime(post.createdAt),
              'Hours Spent': post.hoursSpent || '-',
              Referrer: post.referrerUserId ? (
                <Link to={`/admin/activity/${post.referrerUserId}`}>
                  {post.referrerUserId}
                </Link>
              ) : (
                '-'
              ),
              Club: post.clubId ? (
                <Link to={`/admin/clubs/${post.clubId}`}>{post.clubId}</Link>
              ) : (
                '-'
              ),
              Group: post.groupId ? (
                <Link to={`/admin/groups/${post.groupId}`}>{post.groupId}</Link>
              ) : (
                '-'
              ),
              School: post.schoolId ? (
                <Link to={`/admin/schools/${post.schoolId}`}>
                  {post.schoolId}
                </Link>
              ) : (
                '-'
              ),
              Details: post.details ? post.details : '-',
            }}
          />
        </div>

        <div className="container__row">
          <MetaInformation
            title="Signup Information"
            details={{
              ID: (
                <a href={`/admin/signups/${post.signupId}`}>{post.signupId}</a>
              ),
              User: (
                <Link to={`/admin/users/${post.userId}`}>{post.userId}</Link>
              ),
              Source: post.signup ? post.signup.source : '–',
            }}
          />
        </div>
      </div>
    </div>
  );
};

export default ReviewablePost;
