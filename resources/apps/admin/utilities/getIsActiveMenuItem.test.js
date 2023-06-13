import getIsActiveMenuItem from '@/utilities/getIsActiveMenuItem'
import { BASE_ADMIN_PATH } from '@/constants/pathConstants'

const BASE_PATH = `https://test.givecloud.com${BASE_ADMIN_PATH}`

test('returns false when menuItem is empty', () => {
  const menuItem = {}

  const result = getIsActiveMenuItem({ menuItem })

  expect(result).toBe(false)
})

test(`returns false when menu item url doesn't match current path or have a registered override`, () => {
  const location = {
    pathname: '/test',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: `${BASE_PATH}/random`,
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(false)
})

test('returns true when menu item url matches current pathname', () => {
  const location = {
    pathname: '/test',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: `${BASE_PATH}${location.pathname}`,
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(true)
})

test('returns true when menu item url matches current pathname including query params', () => {
  const location = {
    pathname: '/test',
    search: '?queryone=one&querytwo=two',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: `${BASE_PATH}${location.pathname}${location.search}`,
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(true)
})

test('returns false when has no children with url that matches current pathname', () => {
  const location = {
    pathname: '/test',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: BASE_PATH,
    children: [
      {
        key: 'test_child',
        label: 'Test Label Child',
        url: `${BASE_PATH}/child`,
      },
      {
        key: 'test_child_two',
        label: 'Test Label Child Two',
        url: `${BASE_PATH}/child/2`,
      },
    ],
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(false)
})

test('returns true when has a child with url that matches current pathname', () => {
  const location = {
    pathname: '/two',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: BASE_PATH,
    children: [
      {
        key: 'test_child',
        label: 'Test Label Child',
        url: `${BASE_PATH}/one`,
      },
      {
        key: 'test_child_two',
        label: 'Test Label Child Two',
        url: `${BASE_PATH}${location.pathname}`,
      },
    ],
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(true)
})

test('returns false when children are sections, and no section children have urls that match the current pathname', () => {
  const location = {
    pathname: '/test',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: BASE_PATH,
    children: {
      section1: {
        key: 'section_1',
        label: 'Section 1',
        children: [
          {
            key: 'section_1_test_child',
            label: 'Test Label Child',
            url: `${BASE_PATH}/section_1_child`,
          },
          {
            key: 'section_1_test_child_two',
            label: 'Test Label Child Two',
            url: `${BASE_PATH}/section_1_child/2`,
          },
        ],
      },
      section2: {
        key: 'section_2',
        label: 'Section 2',
        children: [
          {
            key: 'section_2_test_child',
            label: 'Test Label Child',
            url: `${BASE_PATH}/section_2_child`,
          },
          {
            key: 'section_2_test_child_two',
            label: 'Test Label Child Two',
            url: `${BASE_PATH}/section_2_child/2`,
          },
        ],
      },
    },
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(false)
})

test('returns true when children are sections, and a sections child has a url that matches the current pathname', () => {
  const location = {
    pathname: '/section_2_child/2',
  }

  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: BASE_PATH,
    children: {
      section1: {
        key: 'section_1',
        label: 'Section 1',
        children: [
          {
            key: 'section_1_test_child',
            label: 'Test Label Child',
            url: `${BASE_PATH}/section_1_child`,
          },
          {
            key: 'section_1_test_child_two',
            label: 'Test Label Child Two',
            url: `${BASE_PATH}/section_1_child/2`,
          },
        ],
      },
      section2: {
        key: 'section_2',
        label: 'Section 2',
        children: [
          {
            key: 'section_2_test_child',
            label: 'Test Label Child',
            url: `${BASE_PATH}/section_2_child`,
          },
          {
            key: 'section_2_test_child_two',
            label: 'Test Label Child Two',
            url: `${BASE_PATH}${location.pathname}`,
          },
        ],
      },
    },
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(true)
})

test('returns true with a deeply nested structure when contains an item with a url that matches the current pathname', () => {
  const location = {
    pathname: '/child/nested/deep',
  }
  const menuItem = {
    key: 'test',
    label: 'Test Label',
    url: BASE_PATH,
    children: {
      section1: {
        key: 'section_1',
        label: 'Section 1',
        children: [
          {
            key: 'section_1_test_child',
            label: 'Test Label Child',
            url: `${BASE_PATH}/child`,
            children: [
              {
                key: 'section_1_test_child_nested',
                label: 'Test Label Child Nested',
                url: `${BASE_PATH}/child/nested`,
                children: [
                  {
                    key: 'section_1_test_child_nested_deep',
                    label: 'Test Label Child Nested Deep',
                    url: `${BASE_PATH}${location.pathname}`,
                  },
                ],
              },
            ],
          },
        ],
      },
    },
  }

  const result = getIsActiveMenuItem({ menuItem, location })

  expect(result).toBe(true)
})
