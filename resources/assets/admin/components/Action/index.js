import React from 'react';
import gql from 'graphql-tag';
import PropTypes from 'prop-types';

import './action.scss';

export const ActionFragment = gql`
  fragment ActionFragment on Action {
    id
    actionLabel
    anonymous
    campaign {
      id
      internalTitle
      isOpen
    }
    civicAction
    collectSchoolId
    impactGoal
    name
    noun
    online
    postLabel
    postType
    reportback
    scholarshipEntry
    volunteerCredit
    timeCommitmentLabel
    verb
  }
`;

export const Action = ({ action, deleteAction, isPermalink }) => {
  const isAdmin = window.AUTH.role == 'admin';

  return (
    <div className="container__action">
      {!isPermalink ? (
        <div className="container__row">
          <h2>
            <a href={`/admin/actions/${action.id}`}>{action.name}</a>
          </h2>
        </div>
      ) : null}

      <div className="container__row">
        <ul>
          <li>
            <h4>ACTION ID</h4>
            <p>{action.id}</p>
          </li>

          {action.postType === 'phone-call' ? (
            <li>
              <h4>CALLPOWER ID</h4>
              <p>{action.callpowerCampaignId}</p>
            </li>
          ) : null}
        </ul>
      </div>

      <div className="container__row">
        <ul>
          <li>
            <h4>IMPACT GOAL</h4>
            {action.impactGoal ? <p>{action.impactGoal}</p> : <p>–</p>}
          </li>

          <li>
            <h4>NOUN</h4>
            <p>{action.noun}</p>
          </li>

          <li>
            <h4>VERB</h4>
            <p>{action.verb}</p>
          </li>
        </ul>
      </div>

      <div className="container__row">
        <ul>
          <li>
            <h4>POST TYPE</h4>
            <p>{action.postLabel}</p>
          </li>

          <li>
            <h4>ACTION TYPE</h4>
            <p>{action.actionLabel}</p>
          </li>
          <li>
            <h4>TIME COMMITMENT</h4>
            <p>{action.timeCommitmentLabel}</p>
          </li>
        </ul>
      </div>

      <div className="container__row">
        <ul>
          <li>
            <h4>REPORTBACK</h4>
            {action.reportback === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>

          <li>
            <h4>CIVIC ACTION</h4>
            {action.civicAction === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>

          <li>
            <h4>SCHOLARSHIP</h4>
            {action.scholarshipEntry === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>
        </ul>
      </div>

      <div className="container__row">
        <ul>
          <li>
            <h4>ANONYMOUS POST</h4>
            {action.anonymous === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>

          <li>
            <h4>ONLINE ACTION</h4>
            {action.online === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>

          <li>
            <h4>QUIZ ACTION</h4>
            {action.quiz === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>
        </ul>
      </div>

      <div className="container__row">
        <ul>
          <li>
            <h4>COLLECT SCHOOL ID</h4>
            {action.collectSchoolId === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>

          <li>
            <h4>VOLUNTEER CREDIT</h4>
            {action.volunteerCredit === true ? (
              <p className="yes">Yes</p>
            ) : (
              <p className="no">No</p>
            )}
          </li>
        </ul>
      </div>

      {action.campaign && action.campaign.isOpen && !isAdmin ? null : (
        <div className="container__row">
          <a
            className="button -secondary"
            href={`/admin/actions/${action.id}/edit`}
          >
            Edit Action
          </a>

          {deleteAction ? (
            <button
              className="button delete -tertiary"
              onClick={event => deleteAction(action, event)}
            >
              Delete Action
            </button>
          ) : null}
        </div>
      )}
    </div>
  );
};

Action.propTypes = {
  action: PropTypes.object.isRequired,
  isPermalink: PropTypes.bool,
};

Action.defaultProps = {
  isPermalink: false,
};

export default Action;
