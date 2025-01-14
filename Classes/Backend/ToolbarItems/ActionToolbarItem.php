<?php

declare(strict_types=1);

namespace TYPO3\CMS\SysAction\Backend\ToolbarItems;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Doctrine\DBAL\ArrayParameterType;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Toolbar\RequestAwareToolbarItemInterface;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\RootLevelRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\SysAction\ActionTask;

/**
 * Adds action links to the backend's toolbar
 * @internal This is a specific hook implementation and is not considered part of the Public TYPO3 API.
 */
class ActionToolbarItem implements ToolbarItemInterface, RequestAwareToolbarItemInterface
{
    protected array $availableActions = [];
    private ServerRequestInterface $request;

    public function __construct(private readonly UriBuilder $uriBuilder, private readonly BackendViewFactory $backendViewFactory) {}

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Render toolbar icon via Fluid
     *
     * @return string HTML
     */
    public function getItem()
    {
        $view = $this->backendViewFactory->create($this->request, ['friendsoftypo3/sys-action', 'typo3/cms-backend']);
        return $view->render('ToolbarItems/SysActionToolbarItem');
    }

    /**
     * Render drop down
     *
     * @return string HTML
     */
    public function getDropDown()
    {
        $view = $this->backendViewFactory->create($this->request, ['friendsoftypo3/sys-action']);
        $view->assign('actions', $this->availableActions);
        return $view->render('ToolbarItems/SysActionToolbarItemDropDown');
    }

    /**
     * Stores the entries for the action menu in $this->availableActions
     */
    protected function setAvailableActions()
    {
        $actionEntries = [];
        $backendUser = $this->getBackendUser();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_action');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class))
            ->add(GeneralUtility::makeInstance(RootLevelRestriction::class, [
                'sys_action',
            ]));

        $queryBuilder
            ->select('sys_action.*')
            ->from('sys_action');

        if (!empty($GLOBALS['TCA']['sys_action']['ctrl']['sortby'])) {
            $queryBuilder->orderBy('sys_action.' . $GLOBALS['TCA']['sys_action']['ctrl']['sortby']);
        }

        if (!$backendUser->isAdmin()) {
            if (empty($backendUser->userGroupsUID)) {
                $groupList = 0;
            } else {
                $groupList = implode(',', $backendUser->userGroupsUID);
            }

            $queryBuilder
                ->join(
                    'sys_action',
                    'sys_action_asgr_mm',
                    'sys_action_asgr_mm',
                    $queryBuilder->expr()->eq(
                        'sys_action_asgr_mm.uid_local',
                        $queryBuilder->quoteIdentifier('sys_action.uid')
                    )
                )
                ->join(
                    'sys_action_asgr_mm',
                    'be_groups',
                    'be_groups',
                    $queryBuilder->expr()->eq(
                        'sys_action_asgr_mm.uid_foreign',
                        $queryBuilder->quoteIdentifier('be_groups.uid')
                    )
                )
                ->where(
                    $queryBuilder->expr()->in(
                        'be_groups.uid',
                        $queryBuilder->createNamedParameter(
                            GeneralUtility::intExplode(',', $groupList, true),
                            ArrayParameterType::INTEGER
                        )
                    )
                )
                ->groupBy('sys_action.uid');
        }

        $result = $queryBuilder->executeQuery();
        while ($actionRow = $result->fetchAssociative()) {
            $actionRow['link'] = sprintf(
                '%s&SET[mode]=tasks&SET[function]=sys_action.%s&show=%u',
                (string)$this->uriBuilder->buildUriFromRoute('user_task'),
                ActionTask::class, // @todo: class name string is hand over as url parameter?!
                $actionRow['uid']
            );
            $actionEntries[] = $actionRow;
        }

        $this->availableActions = $actionEntries;
    }

    /**
     * This toolbar needs no additional attributes
     *
     * @return array
     */
    public function getAdditionalAttributes()
    {
        return [];
    }

    /**
     * This item has a drop down
     *
     * @return bool
     */
    public function hasDropDown()
    {
        return true;
    }

    /**
     * This toolbar is rendered if there are action entries, no further user restriction
     *
     * @return bool
     */
    public function checkAccess()
    {
        $this->setAvailableActions();
        return !empty($this->availableActions);
    }

    /**
     * Position relative to others
     *
     * @return int
     */
    public function getIndex()
    {
        return 35;
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
