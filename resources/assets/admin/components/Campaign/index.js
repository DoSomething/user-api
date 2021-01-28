import gql from 'graphql-tag';
import { map, isEmpty } from 'lodash';
import React, { useState } from 'react';
import { format, parse } from 'date-fns';
import { useQuery } from '@apollo/react-hooks';

import Action, { ActionFragment } from '../Action';
import Shell from '../utilities/Shell';
import TextBlock from '../utilities/TextBlock';
import EntityLabel from '../utilities/EntityLabel';
import RogueClient from '../../utilities/RogueClient';

import './campaign.scss';

const SHOW_CAMPAIGN_ACTIONS_QUERY = gql`
  query ShowCampaignActionsQuery($id: Int!, $idString: String!) {
    campaign(id: $id) {
      actions {
        ...ActionFragment
      }
      causes {
        id
        name
      }
      createdAt
      endDate
      id
      contentfulCampaignId
      impactDoc
      internalTitle
      startDate
      updatedAt
      groupTypeId
      groupType {
        id
        name
      }
    }
    campaignWebsiteByCampaignId(campaignId: $idString) {
      title
      url
    }
  }
  ${ActionFragment}
`;

const Campaign = ({ id }) => {
  const apiClient = new RogueClient(window.location.origin, {
    headers: {
      Authorization: `Bearer ${window.AUTH.token}`,
    },
  });

  const { loading, error, data } = useQuery(SHOW_CAMPAIGN_ACTIONS_QUERY, {
    variables: { id, idString: `${id}` },
  });

  if (error) {
    return <TextBlock title="Error" content={JSON.stringify(error)} />;
  }

  if (loading) {
    return <TextBlock title="Loading actions..." />;
  }

  function deleteAction(action, event) {
    event.preventDefault();

    const confirmed = confirm(
      `🚨🔥🚨 Are you sure you want to delete Action ID ${action.id}? 🚨🔥🚨`,
    );

    if (confirmed) {
      // Make API request to Rogue to delete the action.
      apiClient
        .delete(`actions/${action.id}`)
        .then(() => {
          window.location.href = `/campaigns/${id}`;
          alert(`Deleted Action ID ${action.id}`);
        })
        .catch(error =>
          alert(
            `Cannot delete Action ID ${action.id}: ${JSON.stringify(error)}`,
          ),
        );
    }
  }

  const { campaign, campaignWebsiteByCampaignId } = data;

  return (
    <div className="container -padded">
      <div className="container__block -narrow -half">
        <h3>Campaign Information</h3>
      </div>
      <div className="container__block -narrow -half">
        <a
          className="button -secondary"
          href={`/campaigns/${campaign.id}/pending`}
        >
          Campaign Inbox
        </a>
      </div>
      <div className="container__block -narrow">
        <h4>Internal Campaign Name</h4>
        <p>{campaign.internalTitle}</p>

        <h4>Campaign ID</h4>
        <p>{campaign.id}</p>

        <h4>Contentful Campaign ID</h4>
        {campaign.contentfulCampaignId ? (
          <p>{campaign.contentfulCampaignId}</p>
        ) : (
          <p>–</p>
        )}

        <h4>Group Type</h4>
        <p>
          {campaign.groupType ? (
            <EntityLabel
              id={campaign.groupType.id}
              name={campaign.groupType.name}
              path="group-types"
            />
          ) : (
            '–'
          )}
        </p>

        <h4>URL</h4>
        <p>
          {campaignWebsiteByCampaignId ? (
            <a href={campaignWebsiteByCampaignId.url} target="_blank">
              {campaignWebsiteByCampaignId.title}
            </a>
          ) : (
            '–'
          )}
        </p>

        <h4>Cause Area</h4>
        <p>
          {campaign.causes.length
            ? campaign.causes.map(cause => cause.name).join(', ')
            : '–'}
        </p>

        <h4>Proof of Impact</h4>
        {campaign.impactDoc ? (
          <p>
            <a href={campaign.impactDoc} target="_blank">
              {campaign.impactDoc}
            </a>
          </p>
        ) : (
          <p>–</p>
        )}
        <h4>Start Date</h4>
        <p>{format(parse(campaign.startDate), 'MM/D/YYYY')}</p>
        <h4>End Date</h4>
        <p>
          {campaign.endDate
            ? format(parse(campaign.endDate), 'MM/D/YYYY')
            : '–'}
        </p>
      </div>
      <div className="container__block -narrow">
        <a className="button" href={`/campaigns/${campaign.id}/edit`}>
          Edit this campaign
        </a>
        <p className="footnote">
          Last updated: {campaign.updatedAt}
          <br />
          Created: {campaign.createdAt}
        </p>
      </div>
      <div id="actions" className="container__block -narrow">
        <h3>Campaign Actions</h3>
        <p>
          Each action in your campaign requires a different set of metadata, to
          determine how it will be treated by Rogue. Use this Action ID in
          Contentful to link user submissions in Rogue.
        </p>
        {!isEmpty(campaign.actions)
          ? map(campaign.actions, (action, key) => {
              return (
                <Action key={key} action={action} deleteAction={deleteAction} />
              );
            })
          : null}
        <div className="container__block -narrow">
          <a
            className="button -secondary"
            href={`/campaigns/${campaign.id}/actions/create`}
          >
            Add Action
          </a>
        </div>
      </div>
    </div>
  );
};

export default Campaign;
